<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GoodsReceiptService
{
    public function __construct(
        private readonly StockMovementService $stockMovementService
    ) {
    }

    public function register(array $data): GoodsReceipt
    {
        return DB::transaction(function () use ($data) {
            $purchase = Purchase::query()
                ->lockForUpdate()
                ->findOrFail($data['purchase_id']);

            if (! $purchase->canReceive()) {
                throw ValidationException::withMessages([
                    'purchase_id' =>
                        'Esta compra não permite novos recebimentos.',
                ]);
            }

            $items = $data['items'] ?? [];

            if (empty($items)) {
                throw ValidationException::withMessages([
                    'items' => 'Informe pelo menos um item recebido.',
                ]);
            }

            $itemIds = array_column($items, 'purchase_item_id');

            if (count($itemIds) !== count(array_unique($itemIds))) {
                throw ValidationException::withMessages([
                    'items' => 'Não repita o mesmo item no recebimento.',
                ]);
            }

            $receipt = GoodsReceipt::create([
                'purchase_id' => $purchase->id,
                'received_at' => $data['received_at'] ?? now(),
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as $receivedItem) {
                $purchaseItem = PurchaseItem::query()
                    ->where('purchase_id', $purchase->id)
                    ->whereKey($receivedItem['purchase_item_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $purchaseItem) {
                    throw ValidationException::withMessages([
                        'items' =>
                            'Um dos itens não pertence a esta compra.',
                    ]);
                }

                $quantity = (float) $receivedItem['quantity'];

                if (! is_finite($quantity) || $quantity <= 0) {
                    throw ValidationException::withMessages([
                        'items' =>
                            'A quantidade recebida deve ser maior que zero.',
                    ]);
                }

                // Trabalha em milésimos para evitar erros de precisão.
                $ordered = (int) round(
                    (float) $purchaseItem->quantity * 1000
                );

                $alreadyReceived = (int) round(
                    (float) $purchaseItem->received_quantity * 1000
                );

                $incoming = (int) round($quantity * 1000);

                if (abs($quantity * 1000 - $incoming) > 0.000001) {
                    throw ValidationException::withMessages([
                        'items' =>
                            'A quantidade aceita no estoque possui até três casas decimais.',
                    ]);
                }

                if ($incoming > $ordered - $alreadyReceived) {
                    throw ValidationException::withMessages([
                        'items' =>
                            'A quantidade recebida ultrapassa o saldo pendente do produto '
                            . $purchaseItem->product->name . '.',
                    ]);
                }

                GoodsReceiptItem::create([
                    'goods_receipt_id' => $receipt->id,
                    'purchase_item_id' => $purchaseItem->id,
                    'quantity' => number_format($incoming / 1000, 3, '.', ''),
                ]);

                $this->stockMovementService->register([
                    'product_id' => $purchaseItem->product_id,
                    'type' => 'IN',
                    'quantity' => number_format(
                        $incoming / 1000,
                        3,
                        '.',
                        ''
                    ),
                    'unit_cost' => $purchaseItem->unit_cost,
                    'reason' => 'COMPRA',
                    'notes' => 'Recebimento da compra '
                        . ($purchase->number ?: '#' . $purchase->id),
                ]);

                $purchaseItem->received_quantity = number_format(
                    ($alreadyReceived + $incoming) / 1000,
                    3,
                    '.',
                    ''
                );

                $purchaseItem->save();
            }

            $purchase->load('items');

            $allReceived = $purchase->items->every(
                fn (PurchaseItem $item) =>
                    (int) round((float) $item->received_quantity * 1000)
                    >= (int) round((float) $item->quantity * 1000)
            );

            $purchase->status = $allReceived
                ? Purchase::STATUS_RECEIVED
                : Purchase::STATUS_PARTIAL;

            $purchase->save();

            return $receipt->load('items.purchaseItem.product');
        }, 3);
    }
}
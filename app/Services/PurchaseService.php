<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\FinancialAccount;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    public function create(array $data): Purchase
    {
        return DB::transaction(function () use ($data) {
            $this->validateSupplier($data['supplier_id']);

            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'number' => $data['number'] ?? null,
                'purchase_date' => $data['purchase_date'],
                'expected_delivery_date' =>
                    $data['expected_delivery_date'] ?? null,
                'status' => Purchase::STATUS_DRAFT,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->replaceItems($purchase, $data['items'] ?? [], $data['discount'] ?? 0);

            return $purchase->load('supplier', 'items.product');
        }, 3);
    }

    public function update(Purchase $purchase, array $data): Purchase
    {
        return DB::transaction(function () use ($purchase, $data) {
            $purchase = Purchase::query()
                ->lockForUpdate()
                ->findOrFail($purchase->id);

            if ($purchase->status !== Purchase::STATUS_DRAFT) {
                throw ValidationException::withMessages([
                    'purchase' =>
                        'Somente compras em rascunho podem ser editadas.',
                ]);
            }

            $this->validateSupplier($data['supplier_id']);

            $purchase->update([
                'supplier_id' => $data['supplier_id'],
                'number' => $data['number'] ?? null,
                'purchase_date' => $data['purchase_date'],
                'expected_delivery_date' =>
                    $data['expected_delivery_date'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->replaceItems(
                $purchase,
                $data['items'] ?? [],
                $data['discount'] ?? 0
            );

            return $purchase->load('supplier', 'items.product');
        }, 3);
    }

    public function confirm(
        Purchase $purchase,
        string $dueDate
    ): Purchase {
        return DB::transaction(function () use ($purchase, $dueDate) {
            $purchase = Purchase::query()
                ->lockForUpdate()
                ->findOrFail($purchase->id);

            if ($purchase->status !== Purchase::STATUS_DRAFT) {
                throw ValidationException::withMessages([
                    'purchase' =>
                        'Somente compras em rascunho podem ser confirmadas.',
                ]);
            }

            if (! $purchase->items()->exists()) {
                throw ValidationException::withMessages([
                    'items' =>
                        'Adicione pelo menos um produto antes de confirmar.',
                ]);
            }

            // Evita gerar uma segunda conta para a mesma compra.
            if (
                FinancialAccount::query()
                    ->where('purchase_id', $purchase->id)
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'purchase' =>
                        'Já existe uma conta financeira vinculada a esta compra.',
                ]);
            }

            $totalCents = (int) round((float) $purchase->total * 100);

            if ($totalCents <= 0) {
                throw ValidationException::withMessages([
                    'purchase' =>
                        'O total da compra deve ser maior que zero para gerar a conta a pagar.',
                ]);
            }

            $supplier = Supplier::query()->find($purchase->supplier_id);

            if (! $supplier || ! $supplier->is_active) {
                throw ValidationException::withMessages([
                    'supplier_id' =>
                        'A compra precisa estar vinculada a um fornecedor ativo.',
                ]);
            }

            FinancialAccount::create([
                'type' => FinancialAccount::TYPE_PAYABLE,
                'supplier_id' => $purchase->supplier_id,
                'purchase_id' => $purchase->id,
                'description' => 'Compra ' . (
                    $purchase->number ?: '#' . $purchase->id
                ),
                'document_number' => $purchase->number,
                'issue_date' => $purchase->purchase_date,
                'due_date' => $dueDate,
                'amount' => number_format(
                    $totalCents / 100,
                    2,
                    '.',
                    ''
                ),
                'discount' => '0.00',
                'interest' => '0.00',
                'status' => FinancialAccount::STATUS_OPEN,
                'notes' => 'Conta gerada automaticamente na confirmação da compra.',
            ]);

            $purchase->update([
                'status' => Purchase::STATUS_CONFIRMED,
            ]);

            return $purchase->load('supplier', 'items.product');
        }, 3);
    }

    private function validateSupplier(int|string $supplierId): void
    {
        $supplier = Supplier::query()->find($supplierId);

        if (! $supplier || ! $supplier->is_active) {
            throw ValidationException::withMessages([
                'supplier_id' =>
                    'Selecione um fornecedor cadastrado e ativo.',
            ]);
        }
    }

    private function replaceItems(
        Purchase $purchase,
        array $items,
        int|float|string $discount
    ): void {
        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => 'Adicione pelo menos um produto à compra.',
            ]);
        }

        $productIds = array_column($items, 'product_id');

        if (count($productIds) !== count(array_unique($productIds))) {
            throw ValidationException::withMessages([
                'items' =>
                    'Cada produto deve aparecer uma única vez na compra.',
            ]);
        }

        $subtotalCents = 0;
        $preparedItems = [];

        foreach ($items as $item) {
            $product = Product::query()->find($item['product_id']);

            if (! $product || ! $product->is_active) {
                throw ValidationException::withMessages([
                    'items' =>
                        'Todos os produtos devem estar cadastrados e ativos.',
                ]);
            }

            $quantity = (float) $item['quantity'];
            $unitCostCents = (int) round(
                (float) $item['unit_cost'] * 100
            );

            if (! is_finite($quantity) || $quantity <= 0) {
                throw ValidationException::withMessages([
                    'items' =>
                        'A quantidade deve ser maior que zero.',
                ]);
            }

            $quantityMilli = (int) round($quantity * 1000);

            if (abs($quantity * 1000 - $quantityMilli) > 0.000001) {
                throw ValidationException::withMessages([
                    'items' =>
                        'A quantidade aceita possui até três casas decimais.',
                ]);
            }

            $lineCents = (int) round(
                ($quantityMilli * $unitCostCents) / 1000
            );

            $subtotalCents += $lineCents;

            $preparedItems[] = [
                'product_id' => $product->id,
                'quantity' => number_format(
                    $quantityMilli / 1000,
                    3,
                    '.',
                    ''
                ),
                'received_quantity' => '0.000',
                'unit_cost' => number_format(
                    $unitCostCents / 100,
                    2,
                    '.',
                    ''
                ),
                'subtotal' => number_format(
                    $lineCents / 100,
                    2,
                    '.',
                    ''
                ),
            ];
        }

        $discountCents = (int) round((float) $discount * 100);

        if ($discountCents < 0 || $discountCents > $subtotalCents) {
            throw ValidationException::withMessages([
                'discount' =>
                    'O desconto deve ser maior ou igual a zero e não pode superar o subtotal.',
            ]);
        }

        // Exclui individualmente para disparar os eventos de auditoria.
        $purchase->items()->get()->each(function ($item) {
            $item->delete();
        });

        // Cria os novos itens utilizando o Eloquent.
        $purchase->items()->createMany($preparedItems);

        $purchase->update([
            'subtotal' => number_format(
                $subtotalCents / 100,
                2,
                '.',
                ''
            ),
            'discount' => number_format(
                $discountCents / 100,
                2,
                '.',
                ''
            ),
            'total' => number_format(
                ($subtotalCents - $discountCents) / 100,
                2,
                '.',
                ''
            ),
        ]);
    }
}
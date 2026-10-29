<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\StockMovementService;

class SaleService
{
    
    public function __construct(
        private readonly StockMovementService $stockMovementService
    ) {
    }

    public function create(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $sale = Sale::create([
                'customer_id' => $data['customer_id'] ?? null,
                'number' => $data['number'] ?? null,
                'sale_date' => $data['sale_date'],
                'status' => Sale::STATUS_DRAFT,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->replaceItems($sale, $data['items'], $data['discount'] ?? 0);

            return $sale->load(['customer', 'items.product']);
        }, 3);
    }

    public function update(Sale $sale, array $data): Sale
    {
        return DB::transaction(function () use ($sale, $data) {
            $sale = Sale::query()
                ->lockForUpdate()
                ->findOrFail($sale->id);

            if (!$sale->isDraft()) {
                throw ValidationException::withMessages([
                    'sale' => 'Somente vendas em rascunho podem ser editadas.',
                ]);
            }

            $sale->update([
                'customer_id' => $data['customer_id'] ?? null,
                'number' => $data['number'] ?? null,
                'sale_date' => $data['sale_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            $this->replaceItems(
                $sale,
                $data['items'],
                $data['discount'] ?? 0
            );

            return $sale->load(['customer', 'items.product']);
        }, 3);
    }

    private function replaceItems(
        Sale $sale,
        array $items,
        mixed $discount
    ): void {
        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => 'Adicione pelo menos um produto à venda.',
            ]);
        }

        $productIds = array_map(
            fn ($item) => (int) $item['product_id'],
            $items
        );

        if (count($productIds) !== count(array_unique($productIds))) {
            throw ValidationException::withMessages([
                'items' => 'Não é permitido repetir o mesmo produto. Ajuste a quantidade na linha existente.',
            ]);
        }

        $subtotalCents = 0;
        $preparedItems = [];

        foreach ($items as $index => $item) {
            $product = Product::query()
                ->whereKey($item['product_id'])
                ->where('is_active', true)
                ->first();

            if (!$product) {
                throw ValidationException::withMessages([
                    "items.{$index}.product_id" =>
                        'Um dos produtos selecionados não existe ou está inativo.',
                ]);
            }

            $quantity = (float) $item['quantity'];

            if (!is_finite($quantity) || $quantity <= 0) {
                throw ValidationException::withMessages([
                    "items.{$index}.quantity" =>
                        'A quantidade deve ser maior que zero.',
                ]);
            }

            // Converte a quantidade para milésimos.
            $quantityMilli = (int) round($quantity * 1000);

            if ($quantityMilli <= 0) {
                throw ValidationException::withMessages([
                    "items.{$index}.quantity" =>
                        'A quantidade informada é muito pequena.',
                ]);
            }

            // O preço é obtido do cadastro, não do formulário.
            // Isso evita que o cliente altere o preço enviado ao servidor.
            $unitPriceCents = (int) round(
                (float) $product->sale_price * 100
            );

            $itemDiscountCents = (int) round(
                (float) ($item['discount'] ?? 0) * 100
            );

            $grossCents = (int) round(
                $quantityMilli * $unitPriceCents / 1000
            );

            if (
                $itemDiscountCents < 0 ||
                $itemDiscountCents > $grossCents
            ) {
                throw ValidationException::withMessages([
                    "items.{$index}.discount" =>
                        'O desconto do item não pode ser negativo nem superar o valor do item.',
                ]);
            }

            $lineTotalCents = $grossCents - $itemDiscountCents;

            $preparedItems[] = [
                'product_id' => $product->id,
                'quantity' => $quantityMilli / 1000,
                'unit_price' => $unitPriceCents / 100,
                'discount' => $itemDiscountCents / 100,
                'subtotal' => $lineTotalCents / 100,
            ];

            $subtotalCents += $lineTotalCents;
        }

        $discountCents = (int) round((float) $discount * 100);

        if ($discountCents < 0 || $discountCents > $subtotalCents) {
            throw ValidationException::withMessages([
                'discount' =>
                    'O desconto geral não pode ser negativo nem superar o subtotal da venda.',
            ]);
        }

        // Só substitui os itens depois de validar todos os dados.
        $sale->items()->delete();
        $sale->items()->createMany($preparedItems);

        $sale->update([
            'subtotal' => $subtotalCents / 100,
            'discount' => $discountCents / 100,
            'total' => ($subtotalCents - $discountCents) / 100,
        ]);
    }


    public function cancelDraft(Sale $sale): Sale
    {
        return DB::transaction(function () use ($sale) {
            $sale = Sale::query()
                ->lockForUpdate()
                ->findOrFail($sale->id);

            if (!$sale->isDraft()) {
                throw ValidationException::withMessages([
                    'sale' => 'Somente vendas em rascunho podem ser canceladas por esta ação.',
                ]);
            }

            $sale->update([
                'status' => Sale::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

            return $sale;
        }, 3);
    }

    
    public function complete(Sale $sale): Sale
    {
        return DB::transaction(function () use ($sale) {
            $sale = Sale::query()
                ->lockForUpdate()
                ->findOrFail($sale->id);

            if (!$sale->isDraft()) {
                throw ValidationException::withMessages([
                    'sale' => 'Somente vendas em rascunho podem ser concluídas.',
                ]);
            }

            $sale->load('items.product');

            if ($sale->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'sale' => 'Não é possível concluir uma venda sem produtos.',
                ]);
            }

            $reference = $sale->number ?: '#' . $sale->id;

            foreach ($sale->items as $item) {
                $product = $item->product;

                if (!$product || !$product->is_active) {
                    throw ValidationException::withMessages([
                        'sale' => 'Um dos produtos da venda está indisponível ou inativo.',
                    ]);
                }

                $this->stockMovementService->register([
                    'product_id' => $item->product_id,
                    'type' => 'OUT',
                    'quantity' => $item->quantity,
                    'unit_cost' => null,
                    'reason' => 'VENDA',
                    'notes' => 'Saída referente à venda ' . $reference,
                ]);
            }

            $sale->update([
                'status' => Sale::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            return $sale->fresh([
                'customer',
                'items.product',
            ]);
        }, 3);
    }

}
<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\StockMovementService;
use App\Models\FinancialAccount;
use App\Services\AttachmentService;

class SaleService
{
    
    public function __construct(
        private readonly StockMovementService $stockMovementService,
        private readonly AttachmentService $attachmentService
    ) {
    }

    public function create(array $data): Sale
    {
        $files = $data['attachments'] ?? [];
        unset($data['attachments']);

        return DB::transaction(function () use ($data, $files) {
            $sale = Sale::create([
                'customer_id' => $data['customer_id'] ?? null,
                'number' => $data['number'] ?? null,
                'sale_date' => $data['sale_date'],
                'status' => Sale::STATUS_DRAFT,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->replaceItems($sale, $data['items'], $data['discount'] ?? 0);

            if (! empty($files)) {
                $this->attachmentService->storeMany($sale, $files);
            }

            return $sale->load(['customer', 'items.product']);
        }, 3);
    }

    public function update(Sale $sale, array $data): Sale
    {
        $files = $data['attachments'] ?? [];
        unset($data['attachments']);

        return DB::transaction(function () use ($sale, $data, $files) {
            $sale = Sale::query()
                ->lockForUpdate()
                ->findOrFail($sale->id);

            if (! $sale->isDraft()) {
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

            if (! empty($files)) {
                $this->attachmentService->storeMany($sale, $files);
            }

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

        // Exclui individualmente os itens antigos para disparar a auditoria.
        $sale->items()->get()->each(function ($item) {
            $item->delete();
        });

        // Cria os novos itens, registrando cada criação na auditoria.
        $sale->items()->createMany($preparedItems);

        $sale->update([
            'subtotal' => $subtotalCents / 100,
            'discount' => $discountCents / 100,
            'total' => ($subtotalCents - $discountCents) / 100,
        ]);
    }


    public function cancel(Sale $sale): Sale
    {
        return DB::transaction(function () use ($sale) {
            $sale = Sale::query()
                ->lockForUpdate()
                ->findOrFail($sale->id);

            if ($sale->status === Sale::STATUS_CANCELLED) {
                throw ValidationException::withMessages([
                    'sale' => 'Esta venda já está cancelada.',
                ]);
            }

            if (! in_array($sale->status, [
                Sale::STATUS_DRAFT,
                Sale::STATUS_COMPLETED,
            ], true)) {
                throw ValidationException::withMessages([
                    'sale' => 'O status atual não permite cancelar esta venda.',
                ]);
            }

            /*
            * Localiza e bloqueia a conta a receber vinculada à venda.
            */
            $account = FinancialAccount::query()
                ->where('sale_id', $sale->id)
                ->lockForUpdate()
                ->first();

            /*
            * Não permite cancelar uma venda com recebimentos registrados.
            * Os registros financeiros são preservados.
            */
            if ($account && $account->transactions()->exists()) {
                throw ValidationException::withMessages([
                    'sale' => 'Não é possível cancelar esta venda porque a conta a receber possui recebimentos registrados. Regularize os recebimentos antes de cancelar.',
                ]);
            }

            /*
            * Cancela a conta a receber na mesma transação da venda.
            */
            if (
                $account
                && $account->status !== FinancialAccount::STATUS_CANCELLED
            ) {
                $account->update([
                    'status' => FinancialAccount::STATUS_CANCELLED,
                ]);
            }

            /*
            * Se a venda já foi concluída, devolve os produtos ao estoque.
            */
            if ($sale->status === Sale::STATUS_COMPLETED) {
                $sale->load('items.product');

                $reference = $sale->number ?: '#' . $sale->id;

                foreach ($sale->items as $item) {
                    $product = $item->product;

                    $this->stockMovementService->register([
                        'product_id' => $item->product_id,
                        'type' => 'IN',
                        'quantity' => $item->quantity,
                        'unit_cost' => $product?->cost_price ?? 0,
                        'reason' => 'ESTORNO_VENDA',
                        'notes' => 'Estorno referente à venda ' . $reference,
                    ]);
                }
            }

            /*
            * Finaliza o cancelamento da venda.
            */
            $sale->update([
                'status' => Sale::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

            return $sale->fresh([
                'customer',
                'items.product',
            ]);
        }, 3);
    }

    
    public function complete(
        Sale $sale,
        string $dueDate
    ): Sale {
        return DB::transaction(function () use ($sale, $dueDate) {
            $sale = Sale::query()
                ->lockForUpdate()
                ->findOrFail($sale->id);

            if (! $sale->isDraft()) {
                throw ValidationException::withMessages([
                    'sale' =>
                        'Somente vendas em rascunho podem ser concluídas.',
                ]);
            }

            $sale->load('items.product');

            if ($sale->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'sale' =>
                        'Não é possível concluir uma venda sem produtos.',
                ]);
            }

            if (
                FinancialAccount::query()
                    ->where('sale_id', $sale->id)
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'sale' =>
                        'Já existe uma conta financeira vinculada a esta venda.',
                ]);
            }

            $totalCents = (int) round((float) $sale->total * 100);

            if ($totalCents <= 0) {
                throw ValidationException::withMessages([
                    'sale' =>
                        'O total da venda deve ser maior que zero para gerar a conta a receber.',
                ]);
            }

            $reference = $sale->number ?: '#' . $sale->id;

            // Baixa o estoque. Qualquer falha reverte toda a transação.
            foreach ($sale->items as $item) {
                $product = $item->product;

                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages([
                        'sale' =>
                            'Um dos produtos da venda está indisponível ou inativo.',
                    ]);
                }

                $this->stockMovementService->register([
                    'product_id' => $item->product_id,
                    'type' => 'OUT',
                    'quantity' => $item->quantity,
                    'unit_cost' => $product->cost_price,
                    'reason' => 'VENDA',
                    'notes' => 'Saída referente à venda ' . $reference,
                ]);
            }

            // Gera a conta a receber dentro da mesma transação.
            FinancialAccount::create([
                'type' => FinancialAccount::TYPE_RECEIVABLE,
                'customer_id' => $sale->customer_id,
                'sale_id' => $sale->id,
                'description' => 'Venda ' . $reference,
                'document_number' => $sale->number,
                'issue_date' => $sale->sale_date,
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
                'notes' => 'Conta gerada automaticamente na conclusão da venda.',
            ]);

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
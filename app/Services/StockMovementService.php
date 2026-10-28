<?php

namespace App\Services;

use App\Models\InventoryStock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockMovementService
{
    /**
     * Registra uma movimentação e atualiza o saldo atomicamente.
     */
    public function register(array $data): StockMovement
    {
        return DB::transaction(function () use ($data) {
            $productId = (int) $data['product_id'];
            $type = $data['type'];

            // Garante que exista uma linha de saldo para o produto.
            // O índice UNIQUE de product_id protege contra duplicidades.
            DB::table('inventory_stocks')->insertOrIgnore([
                'product_id' => $productId,
                'quantity' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Bloqueia a linha até o fim da transação.
            $stock = InventoryStock::query()
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->firstOrFail();

            // Trabalha em milésimos para evitar erros comuns
            // de precisão de ponto flutuante.
            $currentMilli = (int) round(
                (float) $stock->quantity * 1000
            );

            $movementMilli = (int) round(
                (float) $data['quantity'] * 1000
            );

            if ($movementMilli <= 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'A quantidade deve ser maior que zero.',
                ]);
            }

            if ($type === StockMovement::TYPE_IN) {
                $newMilli = $currentMilli + $movementMilli;
            } elseif ($type === StockMovement::TYPE_OUT) {
                $newMilli = $currentMilli - $movementMilli;

                if ($newMilli < 0) {
                    throw ValidationException::withMessages([
                        'quantity' =>
                            'Estoque insuficiente para realizar esta saída.',
                    ]);
                }
            } else {
                throw ValidationException::withMessages([
                    'type' => 'O tipo de movimentação é inválido.',
                ]);
            }

            $newBalance = $newMilli / 1000;

            // Atualiza o saldo atual.
            $stock->update([
                'quantity' => number_format(
                    $newBalance,
                    3,
                    '.',
                    ''
                ),
            ]);

            // Registra o histórico dentro da mesma transação.
            return StockMovement::create([
                'product_id' => $productId,
                'type' => $type,
                'quantity' => number_format(
                    $movementMilli / 1000,
                    3,
                    '.',
                    ''
                ),
                'unit_cost' => $data['unit_cost'] ?? null,
                'reason' => $data['reason'],
                'notes' => $data['notes'] ?? null,
                'balance_after' => number_format(
                    $newBalance,
                    3,
                    '.',
                    ''
                ),
                'moved_at' => now(),
            ]);
        }, 3);
    }
}
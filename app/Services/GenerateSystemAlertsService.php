<?php

namespace App\Services;

use App\Models\InventoryStock;
use App\Models\FinancialAccount;
use App\Models\Purchase;
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use Illuminate\Support\Collection;

class GenerateSystemAlertsService
{
    /**
     * Identifica os alertas e envia notificações
     * para todos os usuários ativos.
     */
    public function execute(): int
    {
        $alerts = $this->collectAlerts();

        $users = User::query()
            ->where('is_active', true)
            ->get();

        $created = 0;

        foreach ($users as $user) {
            foreach ($alerts as $alert) {
                $alreadyExists = $user->notifications()
                    ->where('type', SystemAlertNotification::class)
                    ->whereRaw("(data::jsonb ->> 'alert_key') = ?", [$alert['alert_key']])
                    ->exists();

                if ($alreadyExists) {
                    continue;
                }

                $user->notify(new SystemAlertNotification($alert));

                $created++;
            }
        }

        return $created;
    }

    /**
     * Reúne os alertas dos módulos do ERP.
     */
    private function collectAlerts(): Collection
    {
        return collect()
            ->concat($this->lowStockAlerts())
            ->concat($this->overdueFinancialAlerts())
            ->concat($this->upcomingFinancialAlerts())
            ->concat($this->overduePurchaseAlerts());
    }
    
    /**
     * Produtos com estoque abaixo ou igual ao mínimo.
     */
    private function lowStockAlerts(): Collection
    {
        return InventoryStock::query()
            ->with('product')
            ->whereHas('product', function ($query) {
                $query
                    ->whereNotNull('minimum_stock')
                    ->whereColumn(
                        'inventory_stocks.quantity',
                        '<=',
                        'products.minimum_stock'
                    );
            })
            ->get()
            ->map(function ($stock) {
                $product = $stock->product;

                return [
                    'alert_key' => 'estoque_baixo_' . $stock->id,
                    'type' => 'ESTOQUE_BAIXO',
                    'title' => 'Estoque abaixo do mínimo',
                    'message' => sprintf(
                        'O produto %s está com %s unidades disponíveis. Estoque mínimo: %s.',
                        $product->name,
                        $this->formatQuantity($stock->quantity),
                        $this->formatQuantity($product->minimum_stock)
                    ),
                    'record_id' => $product->id,
                    'url' => null,
                ];
            });
    }

    /**
     * Contas vencidas e ainda não quitadas.
     */
    private function overdueFinancialAlerts(): Collection
    {
        return FinancialAccount::query()
            ->whereIn('status', ['ABERTA', 'PARCIAL'])
            ->whereDate('due_date', '<', today())
            ->get()
            ->map(function ($account) {
                $type = $account->type === 'RECEBER'
                    ? 'receber'
                    : 'pagar';

                return [
                    'alert_key' => 'conta_vencida_' . $account->id,
                    'type' => 'CONTA_VENCIDA',
                    'title' => 'Conta vencida',
                    'message' => sprintf(
                        'A conta a %s "%s", no valor de R$ %s, venceu em %s.',
                        $type,
                        $account->description,
                        number_format((float) $account->amount, 2, ',', '.'),
                        $account->due_date->format('d/m/Y')
                    ),
                    'record_id' => $account->id,
                    'url' => null,
                ];
            });
    }

    /**
     * Contas com vencimento nos próximos sete dias.
     */
    private function upcomingFinancialAlerts(): Collection
    {
        return FinancialAccount::query()
            ->whereIn('status', ['ABERTA', 'PARCIAL'])
            ->whereBetween('due_date', [
                today(),
                today()->addDays(7),
            ])
            ->get()
            ->map(function ($account) {
                $type = $account->type === 'RECEBER'
                    ? 'receber'
                    : 'pagar';

                return [
                    'alert_key' => 'conta_vencendo_' . $account->id,
                    'type' => 'CONTA_VENCENDO',
                    'title' => 'Conta próxima do vencimento',
                    'message' => sprintf(
                        'A conta a %s "%s", no valor de R$ %s, vence em %s.',
                        $type,
                        $account->description,
                        number_format((float) $account->amount, 2, ',', '.'),
                        $account->due_date->format('d/m/Y')
                    ),
                    'record_id' => $account->id,
                    'url' => null,
                ];
            });
    }

    /**
     * Compras confirmadas com entrega prevista atrasada.
     */
    private function overduePurchaseAlerts(): Collection
    {
        return Purchase::query()
            ->whereIn('status', [
                'CONFIRMADA',
                'PARCIALMENTE_RECEBIDA',
            ])
            ->whereNotNull('expected_delivery_date')
            ->whereDate('expected_delivery_date', '<', today())
            ->get()
            ->map(function ($purchase) {
                return [
                    'alert_key' => 'compra_atrasada_' . $purchase->id,
                    'type' => 'COMPRA_ATRASADA',
                    'title' => 'Entrega de compra atrasada',
                    'message' => sprintf(
                        'A compra nº %s tinha previsão de entrega para %s e ainda não foi totalmente recebida.',
                        $purchase->number,
                        $purchase->expected_delivery_date->format('d/m/Y')
                    ),
                    'record_id' => $purchase->id,
                    'url' => null,
                ];
            });
    }

    private function formatQuantity($quantity): string
    {
        return number_format((float) $quantity, 3, ',', '.');
    }
}
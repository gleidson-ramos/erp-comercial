<?php

namespace App\Exports;

use App\Models\FinancialAccount;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class FinancialExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private array $filters = [])
    {
    }

    public function collection(): Collection
    {
        return FinancialAccount::query()
            ->with(['customer', 'supplier'])
            ->withSum('transactions as paid_amount', 'amount')
            ->where('status', '!=', 'CANCELADA')
            ->when($this->filters['date_from'] ?? null, fn ($q, $date) =>
                $q->whereDate('due_date', '>=', $date)
            )
            ->when($this->filters['date_to'] ?? null, fn ($q, $date) =>
                $q->whereDate('due_date', '<=', $date)
            )
            ->when($this->filters['type'] ?? null, fn ($q, $type) =>
                $q->where('type', $type)
            )
            ->when($this->filters['status'] ?? null, fn ($q, $status) =>
                $q->where('status', $status)
            )
            ->orderBy('due_date')
            ->get()
            ->map(function ($account) {
                $paid = (float) ($account->paid_amount ?? 0);

                return [
                    'Vencimento' => $account->due_date?->format('d/m/Y'),
                    'Descrição' => $account->description,
                    'Tipo' => $account->type,
                    'Cliente/Fornecedor' => $account->type === 'RECEBER'
                        ? ($account->customer?->name ?? '-')
                        : ($account->supplier?->name ?? '-'),
                    'Valor' => (float) $account->amount,
                    'Pago/Recebido' => $paid,
                    'Pendente' => max(0, (float) $account->amount - $paid),
                    'Situação' => $account->status,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Vencimento',
            'Descrição',
            'Tipo',
            'Cliente/Fornecedor',
            'Valor',
            'Pago/Recebido',
            'Pendente',
            'Situação',
        ];
    }
}
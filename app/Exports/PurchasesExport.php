<?php

namespace App\Exports;

use App\Models\Purchase;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PurchasesExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private array $filters = [])
    {
    }

    public function collection(): Collection
    {
        return Purchase::query()
            ->with('supplier')
            ->whereNotIn('status', ['RASCUNHO', 'CANCELADA'])
            ->when($this->filters['date_from'] ?? null, fn ($q, $date) =>
                $q->whereDate('purchase_date', '>=', $date)
            )
            ->when($this->filters['date_to'] ?? null, fn ($q, $date) =>
                $q->whereDate('purchase_date', '<=', $date)
            )
            ->when($this->filters['supplier_id'] ?? null, fn ($q, $id) =>
                $q->where('supplier_id', $id)
            )
            ->when($this->filters['product_id'] ?? null, fn ($q, $id) =>
                $q->whereHas('items', fn ($items) =>
                    $items->where('product_id', $id)
                )
            )
            ->orderByDesc('purchase_date')
            ->get()
            ->map(fn ($purchase) => [
                'Número' => $purchase->number,
                'Data' => $purchase->purchase_date?->format('d/m/Y'),
                'Fornecedor' => $purchase->supplier?->name ?? '-',
                'Total' => (float) $purchase->total,
                'Situação' => $purchase->status,
            ]);
    }

    public function headings(): array
    {
        return [
            'Número',
            'Data',
            'Fornecedor',
            'Total',
            'Situação',
        ];
    }
}
<?php

namespace App\Exports;

use App\Models\Sale;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SalesExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private array $filters = [])
    {
    }

    public function collection(): Collection
    {
        return Sale::query()
            ->with('customer')
            ->where('status', 'CONCLUIDA')
            ->when($this->filters['date_from'] ?? null, fn ($q, $date) =>
                $q->whereDate('sale_date', '>=', $date)
            )
            ->when($this->filters['date_to'] ?? null, fn ($q, $date) =>
                $q->whereDate('sale_date', '<=', $date)
            )
            ->when($this->filters['customer_id'] ?? null, fn ($q, $id) =>
                $q->where('customer_id', $id)
            )
            ->when($this->filters['product_id'] ?? null, fn ($q, $id) =>
                $q->whereHas('items', fn ($items) =>
                    $items->where('product_id', $id)
                )
            )
            ->orderByDesc('sale_date')
            ->get()
            ->map(fn ($sale) => [
                'Número' => $sale->number,
                'Data' => $sale->sale_date?->format('d/m/Y'),
                'Cliente' => $sale->customer?->name ?? 'Consumidor não identificado',
                'Subtotal' => (float) $sale->subtotal,
                'Desconto' => (float) $sale->discount,
                'Total' => (float) $sale->total,
                'Situação' => $sale->status,
            ]);
    }

    public function headings(): array
    {
        return [
            'Número',
            'Data',
            'Cliente',
            'Subtotal',
            'Desconto',
            'Total',
            'Situação',
        ];
    }
}
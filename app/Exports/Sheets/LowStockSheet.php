<?php

namespace App\Exports\Sheets;

use App\Models\InventoryStock;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LowStockSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(private array $filters)
    {
    }

    public function collection(): Collection
    {
        return InventoryStock::query()
            ->with('product.unit')
            ->whereHas('product', function ($query) {
                $query->whereNotNull('minimum_stock')
                    ->whereColumn(
                        'inventory_stocks.quantity',
                        '<=',
                        'products.minimum_stock'
                    );
            })
            ->when($this->filters['product_id'] ?? null, fn ($q, $id) =>
                $q->where('product_id', $id)
            )
            ->orderBy('product_id')
            ->get()
            ->map(fn ($stock) => [
                'Produto' => $stock->product?->name ?? '-',
                'Unidade' => $stock->product?->unit?->name ?? '-',
                'Estoque atual' => (float) $stock->quantity,
                'Estoque mínimo' => (float) $stock->product?->minimum_stock,
            ]);
    }

    public function headings(): array
    {
        return [
            'Produto',
            'Unidade',
            'Estoque atual',
            'Estoque mínimo',
        ];
    }

    public function title(): string
    {
        return 'Abaixo do mínimo';
    }
}
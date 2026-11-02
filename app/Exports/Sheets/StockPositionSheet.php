<?php

namespace App\Exports\Sheets;

use App\Models\InventoryStock;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StockPositionSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(private array $filters)
    {
    }

    public function collection(): Collection
    {
        return InventoryStock::query()
            ->with('product.unit')
            ->when($this->filters['product_id'] ?? null, fn ($q, $id) =>
                $q->where('product_id', $id)
            )
            ->orderBy('product_id')
            ->get()
            ->map(fn ($stock) => [
                'Produto' => $stock->product?->name ?? '-',
                'Unidade' => $stock->product?->unit?->name ?? '-',
                'Quantidade' => (float) $stock->quantity,
            ]);
    }

    public function headings(): array
    {
        return ['Produto', 'Unidade', 'Quantidade'];
    }

    public function title(): string
    {
        return 'Posição atual';
    }
}
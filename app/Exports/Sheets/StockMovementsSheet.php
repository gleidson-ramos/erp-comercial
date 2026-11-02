<?php

namespace App\Exports\Sheets;

use App\Models\StockMovement;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class StockMovementsSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    /**
     * Filtros recebidos do relatório de estoque.
     */
    public function __construct(
        private array $filters = []
    ) {
    }

    /**
     * Busca as movimentações de estoque.
     */
    public function collection(): Collection
    {
        return StockMovement::query()
            ->with('product')
            ->when(
                $this->filters['date_from'] ?? null,
                fn ($query, $date) => $query->whereDate('moved_at', '>=', $date)
            )
            ->when(
                $this->filters['date_to'] ?? null,
                fn ($query, $date) => $query->whereDate('moved_at', '<=', $date)
            )
            ->when(
                $this->filters['product_id'] ?? null,
                fn ($query, $id) => $query->where('product_id', $id)
            )
            ->when(
                $this->filters['type'] ?? null,
                fn ($query, $type) => $query->where('type', $type)
            )
            ->orderByDesc('moved_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($movement) => [
                'Data' => $movement->moved_at?->format('d/m/Y H:i'),
                'Produto' => $movement->product?->name ?? '-',
                'Tipo' => $movement->type === 'IN'
                    ? 'Entrada'
                    : 'Saída',
                'Quantidade' => (float) $movement->quantity,
                'Custo unitário' => (float) $movement->unit_cost,
                'Saldo após movimentação' => (float) $movement->balance_after,
                'Motivo' => $movement->reason ?? '-',
                'Observações' => $movement->notes ?? '-',
            ]);
    }

    /**
     * Cabeçalhos da planilha.
     */
    public function headings(): array
    {
        return [
            'Data',
            'Produto',
            'Tipo',
            'Quantidade',
            'Custo unitário',
            'Saldo após movimentação',
            'Motivo',
            'Observações',
        ];
    }

    /**
     * Nome da aba no Excel.
     */
    public function title(): string
    {
        return 'Movimentações';
    }
}
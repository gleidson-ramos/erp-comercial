<?php

namespace App\Exports;

use App\Exports\Sheets\LowStockSheet;
use App\Exports\Sheets\StockMovementsSheet;
use App\Exports\Sheets\StockPositionSheet;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StockExport implements Export, WithMultipleSheets
{
    /**
     * Filtros recebidos do relatório.
     */
    public function __construct(
        private array $filters = []
    ) {
    }

    /**
     * Define as planilhas do arquivo Excel.
     */
    public function sheets(): array
    {
        return [
            new StockPositionSheet($this->filters),
            new LowStockSheet($this->filters),
            new StockMovementsSheet($this->filters),
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Exports\SalesExport;
use App\Exports\PurchasesExport;
use App\Exports\StockExport;
use App\Exports\FinancialExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\ReportController;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportExportController extends Controller
{
    public function sales(Request $request)
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'customer_id' => ['nullable', 'integer'],
            'product_id' => ['nullable', 'integer'],
        ]);

        return Excel::download(
            new SalesExport($filters),
            'relatorio-vendas-' . now()->format('Y-m-d-His') . '.xlsx'
        );
    }

    public function purchases(Request $request)
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'supplier_id' => ['nullable', 'integer'],
            'product_id' => ['nullable', 'integer'],
        ]);

        return Excel::download(
            new PurchasesExport($filters),
            'relatorio-compras-' . now()->format('Y-m-d-His') . '.xlsx'
        );
    }

    public function stock(Request $request)
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'product_id' => ['nullable', 'integer'],
            'type' => ['nullable', 'in:IN,OUT'],
        ]);

        return Excel::download(
            new StockExport($filters),
            'relatorio-estoque-' . now()->format('Y-m-d-His') . '.xlsx'
        );
    }

    public function financial(Request $request)
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'type' => ['nullable', 'in:RECEBER,PAGAR'],
            'status' => ['nullable', 'in:ABERTA,PARCIAL,PAGA'],
        ]);

        return Excel::download(
            new FinancialExport($filters),
            'relatorio-financeiro-' . now()->format('Y-m-d-His') . '.xlsx'
        );
    }

    public function salesPdf(Request $request, ReportController $reportController)
    {
        $report = $reportController->sales($request);

        $pdf = Pdf::loadView(
            'reports.exports.sales',
            $report->getData()
        );

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download(
            'relatorio-vendas-' . now()->format('Y-m-d-His') . '.pdf'
        );
    }

    public function purchasesPdf(Request $request, ReportController $reportController)
    {
        $report = $reportController->purchases($request);

        $pdf = Pdf::loadView(
            'reports.exports.purchases',
            $report->getData()
        );

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download(
            'relatorio-compras-' . now()->format('Y-m-d-His') . '.pdf'
        );
    }

    public function stockPdf(Request $request, ReportController $reportController)
    {
        $report = $reportController->stock($request);

        $pdf = Pdf::loadView(
            'reports.exports.stock',
            $report->getData()
        );

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download(
            'relatorio-estoque-' . now()->format('Y-m-d-His') . '.pdf'
        );
    }

    public function financialPdf(Request $request, ReportController $reportController)
    {
        $report = $reportController->financial($request);

        $pdf = Pdf::loadView(
            'reports.exports.financial',
            $report->getData()
        );

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download(
            'relatorio-financeiro-' . now()->format('Y-m-d-His') . '.pdf'
        );
    }
}
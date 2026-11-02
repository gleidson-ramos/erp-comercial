<?php

namespace App\Http\Controllers;

use App\Exports\SalesExport;
use App\Exports\PurchasesExport;
use App\Exports\StockExport;
use App\Exports\FinancialExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ReportService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ReportExportController extends Controller
{
    private function authorizeReport(string $permission): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless(
            $user?->hasPermission($permission),
            403,
            'Você não possui permissão para exportar este relatório.'
        );
    }

    /**
     * Exportação Excel de vendas.
     */
    public function sales(Request $request)
    {
        $this->authorizeReport('sales.view');

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

    /**
     * Exportação Excel de compras.
     */
    public function purchases(Request $request)
    {
        $this->authorizeReport('purchases.view');

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

    /**
     * Exportação Excel de estoque.
     */
    public function stock(Request $request)
    {
        $this->authorizeReport('stock.view');

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

    /**
     * Exportação Excel financeiro.
     */
    public function financial(Request $request)
    {
        $this->authorizeReport('financial-accounts.view');

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

    /**
     * Exportação PDF de vendas.
     */
    public function salesPdf(
        Request $request,
        ReportController $reportController,
        ReportService $reportService
    ) {
        $this->authorizeReport('sales.view');

        $report = $reportController->sales($request);

        $data = $report->getData();

        $data['sales'] = $reportService->salesForExport(
            $data['filters']
        );

        $pdf = Pdf::loadView(
            'reports.exports.sales',
            $data
        );

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download(
            'relatorio-vendas-' . now()->format('Y-m-d-His') . '.pdf'
        );
    }

    /**
     * Exportação PDF de compras.
     */
    public function purchasesPdf(
        Request $request,
        ReportController $reportController,
        ReportService $reportService
    ) {
        $this->authorizeReport('purchases.view');

        $report = $reportController->purchases($request);

        $data = $report->getData();

        $data['purchases'] = $reportService->purchasesForExport(
            $data['filters']
        );

        $pdf = Pdf::loadView(
            'reports.exports.purchases',
            $data
        );

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download(
            'relatorio-compras-' . now()->format('Y-m-d-His') . '.pdf'
        );
    }

    /**
     * Exportação PDF de estoque.
     */
    public function stockPdf(
        Request $request,
        ReportController $reportController,
        ReportService $reportService
    ) {
        $this->authorizeReport('stock.view');

        $report = $reportController->stock($request);

        $data = $report->getData();

        $filters = [
            'product_id' => $data['filters']['product_id'] ?? null,
            'date_from' => $data['filters']['date_from'],
            'date_to' => $data['filters']['date_to'],
            'type' => $data['filters']['type'] ?? null,
        ];

        $stockFilters = [
            'product_id' => $filters['product_id'],
        ];

        $data['stockPosition'] = $reportService->stockPositionForExport(
            $stockFilters
        );

        $data['lowStockProducts'] = $reportService->productsBelowMinimumStockForExport(
            $stockFilters
        );

        $data['movements'] = $reportService->stockMovementsForExport(
            $filters
        );

        $pdf = Pdf::loadView(
            'reports.exports.stock',
            $data
        );

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download(
            'relatorio-estoque-' . now()->format('Y-m-d-His') . '.pdf'
        );
    }

    /**
     * Exportação PDF financeiro.
     */
    public function financialPdf(
        Request $request,
        ReportController $reportController,
        ReportService $reportService
    ) {
        $this->authorizeReport('financial-accounts.view');

        $report = $reportController->financial($request);

        $data = $report->getData();

        $data['accounts'] = $reportService->financialAccountsForExport(
            $data['filters']
        );

        $pdf = Pdf::loadView(
            'reports.exports.financial',
            $data
        );

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download(
            'relatorio-financeiro-' . now()->format('Y-m-d-His') . '.pdf'
        );
    }
}
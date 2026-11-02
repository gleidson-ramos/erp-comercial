@extends('layouts.app')

@section('title', $module['title'])
@section('page-heading', $module['title'])

@section('content')

    @if(($module['slug'] ?? null) === 'relatorios')
        <div class="row g-3 mt-2">

            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <i class="bi bi-cart-check fs-2 text-primary"></i>
                            <h5 class="fw-semibold mb-0">Relatório de Vendas</h5>
                        </div>

                        <p class="text-muted">
                            Consulte faturamento, clientes e produtos vendidos.
                        </p>

                        <a href="{{ route('reports.sales.index') }}"
                        class="btn btn-outline-primary">
                            Acessar relatório
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <i class="bi bi-truck fs-2 text-success"></i>
                            <h5 class="fw-semibold mb-0">Relatório de Compras</h5>
                        </div>

                        <p class="text-muted">
                            Consulte valores comprados, fornecedores e produtos adquiridos.
                        </p>

                        <a href="{{ route('reports.purchases.index') }}"
                        class="btn btn-outline-primary">
                            Acessar relatório
                        </a>
                    </div>
                </div>
            </div>

            <a href="{{ route('reports.financial.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-cash-stack me-1"></i>
                Relatório Financeiro
            </a>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <i class="bi bi-box-seam fs-2 text-warning"></i>
                            <h5 class="fw-semibold mb-0">Relatório de Estoque</h5>
                        </div>

                        <p class="text-muted">
                            Consulte saldos atuais e produtos que precisam de reposição.
                        </p>

                        <a
                            href="{{ route('reports.stock.index') }}"
                            class="btn btn-outline-primary"
                        >
                            Acessar relatório
                        </a>
                    </div>
                </div>
            </div>

        </div>
    @endif

@endsection
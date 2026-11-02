 @extends('layouts.app')

@section('title', 'Relatórios')
@section('page-heading', 'Relatórios')

@section('content')

<div class="container-fluid">

    <div class="mb-4">
        <h4 class="fw-semibold mb-1">Central de Relatórios</h4>
        <p class="text-muted mb-0">
            Selecione um relatório para consultar informações e indicadores do sistema.
        </p>
    </div>

    <div class="row g-3">

        {{-- Relatório de Vendas --}}
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">

                    <div class="d-flex align-items-center gap-3 mb-3">
                        <i class="bi bi-cart-check fs-2 text-primary"></i>
                        <h5 class="fw-semibold mb-0">Relatório de Vendas</h5>
                    </div>

                    <p class="text-muted flex-grow-1">
                        Consulte faturamento, descontos, ticket médio,
                        clientes e produtos vendidos.
                    </p>

                    <a href="{{ route('reports.sales.index') }}"
                       class="btn btn-outline-primary align-self-start">
                        <i class="bi bi-bar-chart-line me-1"></i>
                        Acessar relatório
                    </a>

                </div>
            </div>
        </div>

        {{-- Relatório de Compras --}}
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">

                    <div class="d-flex align-items-center gap-3 mb-3">
                        <i class="bi bi-truck fs-2 text-success"></i>
                        <h5 class="fw-semibold mb-0">Relatório de Compras</h5>
                    </div>

                    <p class="text-muted flex-grow-1">
                        Consulte valores comprados, fornecedores,
                        produtos adquiridos e histórico de compras.
                    </p>

                    <a href="{{ route('reports.purchases.index') }}"
                       class="btn btn-outline-primary align-self-start">
                        <i class="bi bi-bar-chart-line me-1"></i>
                        Acessar relatório
                    </a>

                </div>
            </div>
        </div>

        {{-- Relatório de Estoque --}}
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">

                    <div class="d-flex align-items-center gap-3 mb-3">
                        <i class="bi bi-box-seam fs-2 text-warning"></i>
                        <h5 class="fw-semibold mb-0">Relatório de Estoque</h5>
                    </div>

                    <p class="text-muted flex-grow-1">
                        Consulte a posição atual do estoque,
                        produtos abaixo do mínimo e histórico de movimentações.
                    </p>

                    <a href="{{ route('reports.stock.index') }}"
                       class="btn btn-outline-primary align-self-start">
                        <i class="bi bi-bar-chart-line me-1"></i>
                        Acessar relatório
                    </a>

                </div>
            </div>
        </div>

        {{-- Relatório Financeiro --}}
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">

                    <div class="d-flex align-items-center gap-3 mb-3">
                        <i class="bi bi-cash-stack fs-2 text-info"></i>
                        <h5 class="fw-semibold mb-0">Relatório Financeiro</h5>
                    </div>

                    <p class="text-muted flex-grow-1">
                        Consulte contas a receber e pagar, valores quitados,
                        pendências e contas vencidas.
                    </p>

                    <a href="{{ route('reports.financial.index') }}"
                       class="btn btn-outline-primary align-self-start">
                        <i class="bi bi-bar-chart-line me-1"></i>
                        Acessar relatório
                    </a>

                </div>
            </div>
        </div>

    </div>

</div>

@endsection
@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-heading', 'Dashboard')

@section('content')
    <div class="page-heading">
        <div>
            <span class="eyebrow">VISÃO GERAL</span>
            <h1>Bem-vindo ao ERP Comercial</h1>
            <p>Uma visão centralizada para a gestão do seu negócio.</p>
        </div>

        <span class="demo-badge">
            <i class="bi bi-info-circle"></i>
            Dados demonstrativos
        </span>
    </div>

    <section class="welcome-panel">
        <div class="welcome-content">
            <span class="welcome-label">GESTÃO SIMPLIFICADA</span>
            <h2>Seu negócio organizado em um só lugar.</h2>
            <p>
                Cadastros, estoque, compras e vendas serão integrados
                progressivamente ao longo da implementação.
            </p>

            <a href="{{ route('modules.show', ['modulo' => 'cadastros']) }}"
               class="btn btn-light">
                Explorar cadastros
                <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>

        <div class="welcome-illustration">
            <i class="bi bi-bar-chart-line"></i>
        </div>
    </section>

    <div class="section-heading">
        <div>
            <h2>Indicadores de exemplo</h2>
            <p>Valores fictícios utilizados apenas para demonstrar o layout.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <article class="metric-card">
                <div class="metric-top">
                    <span>Produtos cadastrados</span>
                    <span class="metric-icon blue"><i class="bi bi-box-seam"></i></span>
                </div>
                <div class="metric-value">128</div>
                <div class="metric-caption">Exemplo ilustrativo</div>
            </article>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <article class="metric-card">
                <div class="metric-top">
                    <span>Clientes</span>
                    <span class="metric-icon green"><i class="bi bi-people"></i></span>
                </div>
                <div class="metric-value">86</div>
                <div class="metric-caption">Exemplo ilustrativo</div>
            </article>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <article class="metric-card">
                <div class="metric-top">
                    <span>Vendas no período</span>
                    <span class="metric-icon purple"><i class="bi bi-cart-check"></i></span>
                </div>
                <div class="metric-value">42</div>
                <div class="metric-caption">Exemplo ilustrativo</div>
            </article>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <article class="metric-card">
                <div class="metric-top">
                    <span>Itens com estoque baixo</span>
                    <span class="metric-icon orange"><i class="bi bi-exclamation-triangle"></i></span>
                </div>
                <div class="metric-value">7</div>
                <div class="metric-caption">Exemplo ilustrativo</div>
            </article>
        </div>
    </div>

    <div class="section-heading">
        <div>
            <h2>Acesso rápido</h2>
            <p>Entre nas áreas do sistema para conhecer sua estrutura.</p>
        </div>
    </div>

    <div class="row g-3">
        @php
            $quickLinks = [
                ['cadastros', 'Cadastros', 'Produtos, clientes e fornecedores', 'bi-collection', 'blue'],
                ['estoque', 'Estoque', 'Saldos e movimentações', 'bi-box-seam', 'green'],
                ['compras', 'Compras', 'Pedidos e recebimentos', 'bi-truck', 'purple'],
                ['vendas', 'Vendas', 'Operações comerciais', 'bi-cart3', 'orange'],
                ['financeiro', 'Financeiro', 'Indicadores fictícios', 'bi-graph-up-arrow', 'green'],
                ['caixa', 'Caixa', 'Interface demonstrativa', 'bi-cash-stack', 'blue'],
            ];
        @endphp

        @foreach ($quickLinks as [$slug, $title, $description, $icon, $color])
            <div class="col-12 col-md-6 col-xl-4">
                <a href="{{ route('modules.show', ['modulo' => $slug]) }}"
                   class="quick-link-card">
                    <span class="quick-link-icon {{ $color }}">
                        <i class="bi {{ $icon }}"></i>
                    </span>
                    <span class="quick-link-text">
                        <strong>{{ $title }}</strong>
                        <small>{{ $description }}</small>
                    </span>
                    <i class="bi bi-arrow-up-right quick-link-arrow"></i>
                </a>
            </div>
        @endforeach
    </div>
@endsection
 @extends('layouts.app')

@section('page-heading', 'Página inicial')

@section('content')
<div class="container-fluid py-3">

    {{-- Cabeçalho de boas-vindas --}}
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-body p-4 p-lg-5">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-primary-subtle text-primary mb-3">
                        ERP COMERCIAL
                    </span>

                    <h2 class="fw-bold mb-2">
                        Olá, {{ auth()->user()->name }}.
                    </h2>

                    <p class="text-muted mb-0">
                        Acesse os módulos disponíveis
                        para acompanhar e gerenciar as operações do negócio.
                    </p>
                </div>

                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <div class="text-muted small">
                        {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}
                    </div>

                    <div class="fw-semibold mt-1">
                        {{ auth()->user()->role }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Resumo operacional --}}
    @if(count($summary))
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h5 class="fw-bold mb-1">Resumo operacional</h5>
                <p class="text-muted small mb-0">
                    Informações atualizadas com os dados do sistema.
                </p>
            </div>
        </div>

        <div class="row g-3 mb-4">
            @foreach($summary as $item)
                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 bg-{{ $item['color'] }}-subtle
                                            text-{{ $item['color'] }}
                                            d-flex align-items-center justify-content-center"
                                     style="width: 48px; height: 48px;">
                                    <i class="bi {{ $item['icon'] }} fs-4"></i>
                                </div>

                                <div>
                                    <div class="text-muted small">
                                        {{ $item['label'] }}
                                    </div>

                                    <div class="fs-4 fw-bold">
                                        {{ number_format($item['value'], 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Módulos --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h5 class="fw-bold mb-1">Acesso aos módulos</h5>
            <p class="text-muted small mb-0">
                Selecione uma área para continuar.
            </p>
        </div>

        <span class="badge bg-light text-dark border">
            {{ $modules->count() }} disponíveis
        </span>
    </div>

    @if($modules->isNotEmpty())
        <div class="row g-3">
            @foreach($modules as $module)
                <div class="col-12 col-md-6 col-xl-4">
                    <a href="{{ $module['url'] }}"
                       class="text-decoration-none text-reset">

                        <div class="card border-0 shadow-sm h-100
                                    module-card">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-start
                                            justify-content-between mb-3">

                                    <div class="rounded-3
                                                bg-{{ $module['color'] }}-subtle
                                                text-{{ $module['color'] }}
                                                d-flex align-items-center
                                                justify-content-center"
                                         style="width: 50px; height: 50px;">
                                        <i class="bi {{ $module['icon'] }} fs-4"></i>
                                    </div>

                                    <i class="bi bi-arrow-up-right
                                              text-muted fs-5"></i>
                                </div>

                                <h6 class="fw-bold mb-2">
                                    {{ $module['title'] }}
                                </h6>

                                <p class="text-muted small mb-0">
                                    {{ $module['description'] }}
                                </p>

                                <div class="mt-4 small fw-semibold
                                            text-primary">
                                    Acessar módulo
                                    <i class="bi bi-arrow-right ms-1"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-grid fs-1 text-muted"></i>

                <h6 class="fw-bold mt-3">
                    Nenhum módulo disponível
                </h6>

                <p class="text-muted small mb-0">
                    Entre em contato com o administrador para solicitar
                    acesso aos módulos do sistema.
                </p>
            </div>
        </div>
    @endif

    <div class="text-center text-muted small mt-5 mb-3">
        ERP Comercial · Sistema de gestão empresarial
    </div>

</div>

<style>
    .module-card {
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .module-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 .5rem 1.5rem rgba(0, 0, 0, .10) !important;
    }
</style>
@endsection
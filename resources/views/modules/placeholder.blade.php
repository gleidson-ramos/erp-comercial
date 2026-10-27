@extends('layouts.app')

@section('title', $module['title'])
@section('page-heading', $module['title'])

@section('content')
    <div class="page-heading">
        <div>
            <span class="eyebrow">MÓDULO DO SISTEMA</span>
            <h1>{{ $module['title'] }}</h1>
            <p>{{ $module['description'] }}</p>
        </div>
    </div>

    @if (in_array($module['status'], ['Demonstração']))
        <div class="alert alert-warning demo-alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Modo demonstrativo:</strong>
            esta área não registra operações financeiras reais.
            Os dados e indicadores apresentados aqui, quando houver,
            serão fictícios.
        </div>
    @endif

    <section class="module-panel">
        <div class="module-panel-icon">
            <i class="bi {{ $module['icon'] }}"></i>
        </div>

        <span class="module-status">{{ $module['status'] }}</span>

        <h2>{{ $module['title'] }}</h2>
        <p>{{ $module['description'] }}</p>

        <div class="module-divider"></div>

        <p class="module-help">
            Esta página é a estrutura inicial do módulo. As funcionalidades
            serão implementadas e testadas em etapas, com validação dos
            dados e regras de negócio antes de serem consideradas concluídas.
        </p>

        <a href="{{ route('dashboard') }}" class="btn btn-primary">
            <i class="bi bi-arrow-left me-2"></i>
            Voltar ao dashboard
        </a>
    </section>
@endsection
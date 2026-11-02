
@extends('layouts.app')

@section('title', 'Detalhes da auditoria')

@section('content')
<div class="container-fluid">

    {{-- Cabeçalho --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Detalhes da auditoria</h1>
            <p class="text-muted mb-0">
                Consulte os detalhes da operação registrada no sistema.
            </p>
        </div>

        <a href="{{ route('audit-logs.index') }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Voltar
        </a>
    </div>

    {{-- Informações gerais --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Informações da operação</h5>
        </div>

        <div class="card-body">
            <div class="row g-4">

                <div class="col-md-3">
                    <label class="text-muted small">Evento</label>
                    <div class="mt-1">
                        @switch($auditLog->event)
                            @case('created')
                                <span class="badge bg-success">Criação</span>
                                @break

                            @case('updated')
                                <span class="badge bg-primary">Alteração</span>
                                @break

                            @case('deleted')
                                <span class="badge bg-danger">Exclusão</span>
                                @break

                            @default
                                <span class="badge bg-secondary">
                                    {{ $auditLog->event }}
                                </span>
                        @endswitch
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="text-muted small">Data e hora</label>
                    <div class="fw-semibold mt-1">
                        {{ $auditLog->created_at?->format('d/m/Y H:i:s') }}
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="text-muted small">Módulo</label>
                    <div class="fw-semibold mt-1">
                        {{ class_basename($auditLog->auditable_type) }}
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="text-muted small">ID do registro</label>
                    <div class="fw-semibold mt-1">
                        #{{ $auditLog->auditable_id }}
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="text-muted small">Usuário responsável</label>
                    <div class="fw-semibold mt-1">
                        {{ $auditLog->user_name ?? 'Usuário não identificado' }}
                    </div>
                    <small class="text-muted">
                        {{ $auditLog->user_email ?? 'Sem e-mail registrado' }}
                    </small>
                </div>

                <div class="col-md-6">
                    <label class="text-muted small">Endereço IP</label>
                    <div class="fw-semibold mt-1">
                        {{ $auditLog->ip_address ?? 'Não registrado' }}
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Alterações realizadas --}}
    @php
        $oldValues = $auditLog->old_values ?? [];
        $newValues = $auditLog->new_values ?? [];

        $fields = array_unique(array_merge(
            array_keys($oldValues),
            array_keys($newValues)
        ));
    @endphp

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Detalhamento dos dados</h5>
        </div>

        <div class="card-body p-0">
            @if (count($fields) > 0)

                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">

                        <thead class="table-light">
                            <tr>
                                <th style="width: 25%">Campo</th>
                                <th style="width: 37.5%">Valor anterior</th>
                                <th style="width: 37.5%">Valor novo</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($fields as $field)
                                @php
                                    $oldValue = $oldValues[$field] ?? null;
                                    $newValue = $newValues[$field] ?? null;

                                    $formatValue = function ($value) {
                                        if ($value === null) {
                                            return 'Não informado';
                                        }

                                        if (is_bool($value)) {
                                            return $value ? 'Sim' : 'Não';
                                        }

                                        if (is_array($value)) {
                                            return json_encode(
                                                $value,
                                                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                                            );
                                        }

                                        return (string) $value;
                                    };
                                @endphp

                                <tr>
                                    <td class="fw-semibold">
                                        {{ str_replace('_', ' ', ucfirst($field)) }}
                                    </td>

                                    <td>
                                        <div class="text-break">
                                            {{ $formatValue($oldValue) }}
                                        </div>
                                    </td>

                                    <td>
                                        <div class="text-break">
                                            {{ $formatValue($newValue) }}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>

            @else
                <div class="text-center py-5">
                    <i class="bi bi-info-circle fs-2 text-muted"></i>
                    <p class="text-muted mt-2 mb-0">
                        Não existem detalhes de campos registrados nesta operação.
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- Informações técnicas --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Informações técnicas</h5>
        </div>

        <div class="card-body">
            <div class="mb-3">
                <label class="text-muted small">URL da operação</label>
                <div class="text-break">
                    {{ $auditLog->url ?? 'Não registrada' }}
                </div>
            </div>

            <div>
                <label class="text-muted small">Navegador e dispositivo</label>
                <div class="text-break">
                    {{ $auditLog->user_agent ?? 'Não registrado' }}
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
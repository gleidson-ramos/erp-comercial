@extends('layouts.app')

@section('title', 'Central de Notificações')

@section('content')
<div class="container-fluid">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="mb-1">Central de Notificações</h4>
            <p class="text-muted mb-0">
                Acompanhe os alertas e acontecimentos do ERP.
            </p>
        </div>

        @if($unreadCount > 0)
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                @method('PATCH')

                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-check2-all me-1"></i>
                    Marcar todas como lidas
                </button>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <span class="text-muted small">Não lidas</span>
                    <h3 class="mb-0 mt-2">{{ $unreadCount }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <span class="text-muted small">Exibidas nesta página</span>
                    <h3 class="mb-0 mt-2">{{ $notifications->count() }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <span class="text-muted small">Total de notificações</span>
                    <h3 class="mb-0 mt-2">{{ $notifications->total() }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('notifications.index') }}">
                <div class="row g-3 align-items-end">

                    <div class="col-md-5">
                        <label for="type" class="form-label">Tipo de alerta</label>

                        <select name="type" id="type" class="form-select">
                            <option value="">Todos os tipos</option>

                            <option value="ESTOQUE_BAIXO"
                                @selected($filters['type'] === 'ESTOQUE_BAIXO')>
                                Estoque baixo
                            </option>

                            <option value="CONTA_VENCIDA"
                                @selected($filters['type'] === 'CONTA_VENCIDA')>
                                Contas vencidas
                            </option>

                            <option value="CONTA_VENCENDO"
                                @selected($filters['type'] === 'CONTA_VENCENDO')>
                                Contas próximas do vencimento
                            </option>

                            <option value="COMPRA_ATRASADA"
                                @selected($filters['type'] === 'COMPRA_ATRASADA')>
                                Compras atrasadas
                            </option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="status" class="form-label">Situação</label>

                        <select name="status" id="status" class="form-select">
                            <option value="TODAS"
                                @selected($filters['status'] === 'TODAS')>
                                Todas
                            </option>

                            <option value="NAO_LIDAS"
                                @selected($filters['status'] === 'NAO_LIDAS')>
                                Não lidas
                            </option>

                            <option value="LIDAS"
                                @selected($filters['status'] === 'LIDAS')>
                                Lidas
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-funnel me-1"></i>
                            Filtrar
                        </button>

                        <a href="{{ route('notifications.index') }}"
                           class="btn btn-outline-secondary"
                           title="Limpar filtros">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Notificações</h5>
        </div>

        <div class="card-body p-0">

            @forelse($notifications as $notification)
                @php
                    $data = $notification->data;

                    $types = [
                        'ESTOQUE_BAIXO' => [
                            'label' => 'Estoque baixo',
                            'class' => 'warning',
                            'icon' => 'bi-box-seam',
                        ],
                        'CONTA_VENCIDA' => [
                            'label' => 'Conta vencida',
                            'class' => 'danger',
                            'icon' => 'bi-calendar-x',
                        ],
                        'CONTA_VENCENDO' => [
                            'label' => 'Vencimento próximo',
                            'class' => 'info',
                            'icon' => 'bi-calendar-event',
                        ],
                        'COMPRA_ATRASADA' => [
                            'label' => 'Compra atrasada',
                            'class' => 'danger',
                            'icon' => 'bi-truck',
                        ],
                    ];

                    $type = $types[$data['type'] ?? ''] ?? [
                        'label' => 'Notificação',
                        'class' => 'secondary',
                        'icon' => 'bi-bell',
                    ];
                @endphp

                <div class="p-3 border-bottom {{ is_null($notification->read_at) ? 'bg-light' : '' }}">
                    <div class="d-flex align-items-start gap-3">

                        <div class="rounded-circle bg-white border d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width: 42px; height: 42px;">
                            <i class="bi {{ $type['icon'] }} text-{{ $type['class'] }}"></i>
                        </div>

                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <strong>{{ $data['title'] ?? 'Notificação' }}</strong>

                                <span class="badge bg-{{ $type['class'] }}">
                                    {{ $type['label'] }}
                                </span>

                                @if(is_null($notification->read_at))
                                    <span class="badge bg-primary">Não lida</span>
                                @else
                                    <span class="badge bg-secondary">Lida</span>
                                @endif
                            </div>

                            <p class="text-muted mb-1">
                                {{ $data['message'] ?? '' }}
                            </p>

                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>
                                {{ $notification->created_at->format('d/m/Y H:i') }}
                            </small>
                        </div>

                        @if(is_null($notification->read_at))
                            <form action="{{ route('notifications.read', $notification->id) }}"
                                  method="POST">
                                @csrf
                                @method('PATCH')

                                <button type="submit"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Marcar como lida">
                                    <i class="bi bi-check2"></i>
                                </button>
                            </form>
                        @endif

                    </div>
                </div>

            @empty
                <div class="text-center py-5">
                    <i class="bi bi-bell-slash display-5 text-muted"></i>

                    <h6 class="mt-3">Nenhuma notificação encontrada</h6>

                    <p class="text-muted mb-0">
                        Não existem notificações para os filtros selecionados.
                    </p>
                </div>
            @endforelse

        </div>

        @if($notifications->hasPages())
            <div class="card-footer bg-white">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
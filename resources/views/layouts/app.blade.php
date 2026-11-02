<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Início') | ERP Comercial</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="{{ asset('css/erp.css') }}">
</head>
<body>
    @php
        $user = auth()->user();

        $homeUrl = route('home');

        $canAccessModule = fn (array $permissions) =>
            $user->hasAnyPermission($permissions);
    @endphp


    <div class="app-shell">
        <aside class="sidebar" id="sidebar">
            <a href="{{ $homeUrl }}" class="brand">
                <span class="brand-icon">
                    <i class="bi bi-grid-1x2-fill"></i>
                </span>
                <span>
                    <strong>ERP Comercial</strong>
                    <small>Gestão simplificada</small>
                </span>
            </a>

            <div class="sidebar-label">PRINCIPAL</div>

            <nav class="sidebar-nav">

                <a href="{{ route('home') }}"
                class="nav-link-custom {{ request()->routeIs('home') ? 'active' : '' }}">
                    <i class="bi bi-house-door"></i>
                    <span>Início</span>
                </a>

                @if ($user->hasPermission('dashboard.view'))
                    <a href="{{ route('dashboard') }}"
                    class="nav-link-custom {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                @endif

            </nav>

            <div class="sidebar-label">OPERAÇÃO</div>

            <nav class="sidebar-nav">                
                @if ($canAccessModule(['customers.view', 'customers.create']))
                    <a href="{{ route($user->hasPermission('customers.view') ? 'customers.index' : 'customers.create') }}"
                    class="nav-link-custom {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i>
                        <span>Clientes</span>
                    </a>
                @endif

                @if ($canAccessModule(['suppliers.view', 'suppliers.create']))
                    <a href="{{ route($user->hasPermission('suppliers.view') ? 'suppliers.index' : 'suppliers.create') }}"
                    class="nav-link-custom {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                        <i class="bi bi-truck"></i>
                        <span>Fornecedores</span>
                    </a>
                @endif

                @if ($canAccessModule(['products.view', 'products.create']))
                    <a href="{{ route($user->hasPermission('products.view') ? 'products.index' : 'products.create') }}"
                    class="nav-link-custom {{ request()->routeIs('products.*') ? 'active' : '' }}">
                        <i class="bi bi-box-seam"></i>
                        <span>Produtos</span>
                    </a>
                @endif

                @if ($canAccessModule(['categories.view', 'categories.create']))
                    <a href="{{ route($user->hasPermission('categories.view') ? 'categories.index' : 'categories.create') }}"
                    class="nav-link-custom {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                        <i class="bi bi-tags"></i>
                        <span>Categorias</span>
                    </a>
                @endif

                @if ($canAccessModule(['units.view', 'units.create']))
                    <a href="{{ route($user->hasPermission('units.view') ? 'units.index' : 'units.create') }}"
                    class="nav-link-custom {{ request()->routeIs('units.*') ? 'active' : '' }}">
                        <i class="bi bi-rulers"></i>
                        <span>Unidades de medida</span>
                    </a>
                @endif
                
                @if ($user->hasPermission('stock.view'))
                    <a href="{{ route('stock.index') }}"
                    class="nav-link-custom {{ request()->routeIs('stock.*') ? 'active' : '' }}">
                        <i class="bi bi-boxes"></i>
                        <span>Estoque</span>
                    </a>
                @endif

                @if ($canAccessModule(['purchases.view', 'purchases.create']))
                    <a href="{{ route($user->hasPermission('purchases.view') ? 'purchases.index' : 'purchases.create') }}"
                    class="nav-link-custom {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                        <i class="bi bi-bag"></i>
                        <span>Compras</span>
                    </a>
                @endif

                @if ($canAccessModule(['sales.view', 'sales.create']))
                    <a href="{{ route($user->hasPermission('sales.view') ? 'sales.index' : 'sales.create') }}"
                    class="nav-link-custom {{ request()->routeIs('sales.*') ? 'active' : '' }}">
                        <i class="bi bi-cart3"></i>
                        <span>Vendas</span>
                    </a>
                @endif
            </nav>

            <nav class="sidebar-nav">

                @if ($canAccessModule([
                    'financial-accounts.view',
                    'financial-accounts.create'
                ]))
                    <a href="{{ route(
                        $user->hasPermission('financial-accounts.view')
                            ? 'financial-accounts.index'
                            : 'financial-accounts.create'
                    ) }}"
                    class="nav-link-custom {{ request()->routeIs('financial-accounts.*') ? 'active' : '' }}">
                        <i class="bi bi-cash-stack"></i>
                        <span>Financeiro</span>
                    </a>
                @endif

                @if ($user->hasPermission('cash-flow.view'))
                    <a href="{{ route('financial.cash-flow.index') }}"
                    class="nav-link-custom {{ request()->routeIs('financial.cash-flow.*') ? 'active' : '' }}">
                        <i class="bi bi-graph-up-arrow"></i>
                        <span>Fluxo de Caixa</span>
                    </a>
                @endif

                @if ($user->hasPermission('modules.view'))
                    <a href="{{ route('reports.index') }}"
                    class="nav-link-custom {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                        <span>Relatórios</span>
                    </a>
                @endif

                @if ($user->canManageUsers())
                    <a href="{{ route('users.index') }}"
                    class="nav-link-custom {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="bi bi-person-gear"></i>
                        <span>Usuários</span>
                    </a>

                    <a href="{{ route('audit-logs.index') }}"
                    class="nav-link-custom {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                        <i class="bi bi-journal-text"></i>
                        <span>Auditoria</span>
                    </a>
                @endif

            </nav>

            <div class="sidebar-bottom">

                @if ($user->hasPermission('modules.view'))
                    <a href="{{ route('modules.show', ['modulo' => 'configuracoes']) }}"
                    class="nav-link-custom {{ request()->is('modulos/configuracoes') ? 'active' : '' }}">
                        <i class="bi bi-gear"></i>
                        <span>Configurações</span>
                    </a>
                @endif

                <div class="environment-note">
                    <i class="bi bi-info-circle"></i>
                    <span>Ambiente em desenvolvimento</span>
                </div>

            </div>
        </aside>

        <div class="main-area">
            <header class="topbar">
                <button class="mobile-menu-button"
                        id="mobileMenuButton"
                        type="button"
                        aria-label="Abrir menu"
                        aria-controls="sidebar"
                        aria-expanded="false">
                    <i class="bi bi-list"></i>
                </button>

                <div class="topbar-context">
                    <span class="topbar-eyebrow">ERP COMERCIAL</span>
                    <span class="topbar-title">@yield('page-heading', 'Visão geral')</span>
                </div>
                
                <div class="topbar-right">
                    <span class="environment-badge">
                        <span></span> Desenvolvimento
                    </span>

                    <div class="d-flex align-items-center gap-2">
                        <div class="user-avatar"
                            title="{{ Auth::user()->name }}">
                            <i class="bi bi-person"></i>
                        </div>

                        <span class="d-none d-md-inline fw-semibold">
                            {{ Auth::user()->name }}
                        </span>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button type="submit"
                                    class="btn btn-outline-secondary btn-sm"
                                    title="Sair do sistema">
                                <i class="bi bi-box-arrow-right"></i>
                                <span class="d-none d-lg-inline">Sair</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="page-content">
                @yield('content')
            </main>

            <footer class="app-footer">
                <span>ERP Comercial</span>
                <span>Projeto em desenvolvimento</span>
            </footer>
        </div>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const menuButton = document.getElementById('mobileMenuButton');
        const overlay = document.getElementById('sidebarOverlay');

        function setMenuOpen(open) {
            sidebar.classList.toggle('sidebar-open', open);
            overlay.classList.toggle('overlay-visible', open);
            menuButton.setAttribute('aria-expanded', String(open));
        }

        menuButton.addEventListener('click', () => {
            setMenuOpen(!sidebar.classList.contains('sidebar-open'));
        });

        overlay.addEventListener('click', () => setMenuOpen(false));

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') setMenuOpen(false);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
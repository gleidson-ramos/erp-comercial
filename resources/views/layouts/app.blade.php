<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Dashboard') | ERP Comercial</title>

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
    <div class="app-shell">
        <aside class="sidebar" id="sidebar">
            <a href="{{ route('dashboard') }}" class="brand">
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
                <a href="{{ route('dashboard') }}"
                   class="nav-link-custom {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-house-door"></i>
                    <span>Dashboard</span>
                </a>
            </nav>

            <div class="sidebar-label">OPERAÇÃO</div>

            <nav class="sidebar-nav">
                <a href="{{ route('modules.show', ['modulo' => 'cadastros']) }}"
                   class="nav-link-custom {{ request()->is('modulos/cadastros') ? 'active' : '' }}">
                    <i class="bi bi-collection"></i><span>Cadastros</span>
                </a>
                <a href="{{ route('products.index') }}"
                class="nav-link-custom {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <i class="bi bi-collection"></i>
                    <span>Cadastros / Produtos</span>
                </a>

                <a href="{{ route('modules.show', ['modulo' => 'estoque']) }}"
                   class="nav-link-custom {{ request()->is('modulos/estoque') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i><span>Estoque</span>
                </a>

                <a href="{{ route('modules.show', ['modulo' => 'compras']) }}"
                   class="nav-link-custom {{ request()->is('modulos/compras') ? 'active' : '' }}">
                    <i class="bi bi-truck"></i><span>Compras</span>
                </a>

                <a href="{{ route('modules.show', ['modulo' => 'vendas']) }}"
                   class="nav-link-custom {{ request()->is('modulos/vendas') ? 'active' : '' }}">
                    <i class="bi bi-cart3"></i><span>Vendas</span>
                </a>
            </nav>

            <div class="sidebar-label">GESTÃO</div>

            <nav class="sidebar-nav">
                <a href="{{ route('modules.show', ['modulo' => 'financeiro']) }}"
                   class="nav-link-custom {{ request()->is('modulos/financeiro') ? 'active' : '' }}">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Financeiro</span>
                    <span class="demo-dot" title="Modo demonstrativo"></span>
                </a>

                <a href="{{ route('modules.show', ['modulo' => 'caixa']) }}"
                   class="nav-link-custom {{ request()->is('modulos/caixa') ? 'active' : '' }}">
                    <i class="bi bi-cash-stack"></i>
                    <span>Caixa</span>
                    <span class="demo-dot" title="Modo demonstrativo"></span>
                </a>

                <a href="{{ route('modules.show', ['modulo' => 'relatorios']) }}"
                   class="nav-link-custom {{ request()->is('modulos/relatorios') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-bar-graph"></i><span>Relatórios</span>
                </a>
            </nav>

            <div class="sidebar-bottom">
                <a href="{{ route('modules.show', ['modulo' => 'configuracoes']) }}"
                   class="nav-link-custom {{ request()->is('modulos/configuracoes') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i><span>Configurações</span>
                </a>

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
                    <div class="user-avatar" title="Usuário de demonstração">
                        <i class="bi bi-person"></i>
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
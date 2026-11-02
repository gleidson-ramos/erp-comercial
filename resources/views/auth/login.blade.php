<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Entrar | ERP Comercial</title>

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
<body class="bg-light">

<div class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
    <div class="card border-0 shadow-sm rounded-4 w-100" style="max-width: 430px;">
        <div class="card-body p-4 p-md-5">

            <div class="text-center mb-4">
                <div class="mb-3">
                    <span class="d-inline-flex align-items-center justify-content-center
                                 rounded-3 bg-primary text-white"
                          style="width: 58px; height: 58px; font-size: 25px;">
                        <i class="bi bi-grid-1x2-fill"></i>
                    </span>
                </div>

                <h4 class="fw-bold mb-1">ERP Comercial</h4>
                <p class="text-muted mb-0">Entre com suas credenciais</p>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                            aria-label="Fechar"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">
                        E-mail
                    </label>

                    <input
                        type="email"
                        class="form-control @error('email') is-invalid @enderror"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="seu@email.com"
                        autocomplete="username"
                        required
                        autofocus
                    >
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">
                        Senha
                    </label>

                    <input
                        type="password"
                        class="form-control @error('password') is-invalid @enderror"
                        id="password"
                        name="password"
                        placeholder="Digite sua senha"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <div class="form-check mb-4">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="remember"
                        value="1"
                        id="remember"
                    >

                    <label class="form-check-label text-muted" for="remember">
                        Manter conectado
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">
                    <i class="bi bi-box-arrow-in-right me-1"></i>
                    Entrar no sistema
                </button>
            </form>

            <div class="text-center mt-4">
                <small class="text-muted">
                    ERP Comercial · Ambiente interno
                </small>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
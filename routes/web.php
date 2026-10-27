<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/modulos/{modulo}', function (string $modulo) {
    $modules = [
        'cadastros' => [
            'title' => 'Cadastros',
            'description' => 'Gerencie produtos, categorias, clientes e fornecedores.',
            'icon' => 'bi-collection',
            'status' => 'Em desenvolvimento',
        ],
        'estoque' => [
            'title' => 'Estoque',
            'description' => 'Consulte saldos, entradas, saídas e movimentações.',
            'icon' => 'bi-box-seam',
            'status' => 'Planejado',
        ],
        'compras' => [
            'title' => 'Compras',
            'description' => 'Organize pedidos e recebimentos de mercadorias.',
            'icon' => 'bi-truck',
            'status' => 'Planejado',
        ],
        'vendas' => [
            'title' => 'Vendas',
            'description' => 'Registre vendas e acompanhe suas operações.',
            'icon' => 'bi-cart3',
            'status' => 'Planejado',
        ],
        'financeiro' => [
            'title' => 'Financeiro demonstrativo',
            'description' => 'Visualize uma prévia dos indicadores financeiros com dados fictícios.',
            'icon' => 'bi-graph-up-arrow',
            'status' => 'Demonstração',
        ],
        'caixa' => [
            'title' => 'Caixa demonstrativo',
            'description' => 'Conheça a interface prevista para o controle de caixa.',
            'icon' => 'bi-cash-stack',
            'status' => 'Demonstração',
        ],
        'relatorios' => [
            'title' => 'Relatórios',
            'description' => 'Área reservada para relatórios e indicadores gerenciais.',
            'icon' => 'bi-file-earmark-bar-graph',
            'status' => 'Planejado',
        ],
        'configuracoes' => [
            'title' => 'Configurações',
            'description' => 'Área reservada para os parâmetros do sistema.',
            'icon' => 'bi-gear',
            'status' => 'Planejado',
        ],
    ];

    abort_unless(array_key_exists($modulo, $modules), 404);

    return view('modules.placeholder', [
        'module' => $modules[$modulo],
    ]);
})->name('modules.show');
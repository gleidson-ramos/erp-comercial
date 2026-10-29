<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\SaleController;


Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

#Produtos
Route::resource('produtos', ProductController::class)
    ->except(['show', 'destroy'])
    ->parameters(['produtos' => 'product'])
    ->names('products');

Route::patch(
    '/produtos/{product}/desativar',
    [ProductController::class, 'deactivate']
)->name('products.deactivate');

#Categorias
Route::resource('categorias', CategoryController::class)
    ->except(['show', 'destroy'])
    ->parameters(['categorias' => 'category'])
    ->names('categories');

Route::patch(
    '/categorias/{category}/desativar',
    [CategoryController::class, 'deactivate']
)->name('categories.deactivate');

Route::resource('unidades', UnitController::class)
    ->except(['show', 'destroy'])
    ->parameters(['unidades' => 'unit'])
    ->names('units');

Route::patch(
    '/unidades/{unit}/desativar',
    [UnitController::class, 'deactivate']
)->name('units.deactivate');

#Estoque
Route::prefix('estoque')
    ->name('stock.')
    ->controller(StockController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/movimentacoes/criar', 'create')->name('create');
        Route::post('/movimentacoes', 'store')->name('store');
        Route::get('/historico', 'history')->name('history');
    });

#Cliente
Route::resource('clientes', CustomerController::class)
    ->except(['show', 'destroy'])
    ->parameters(['clientes' => 'customer'])
    ->names('customers');


#Fornecedores
Route::prefix('fornecedores')
    ->name('suppliers.')
    ->controller(SupplierController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{supplier}/edit', 'edit')->name('edit');
        Route::put('/{supplier}', 'update')->name('update');
        Route::patch('/{supplier}/deactivate', 'deactivate')->name('deactivate');
    });


#Compras
Route::prefix('compras')
    ->name('purchases.')
    ->controller(PurchaseController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{purchase}/recebimentos/create',[GoodsReceiptController::class, 'create'])->name('receipts.create');
        Route::post('/{purchase}/recebimentos', [GoodsReceiptController::class, 'store'])->name('receipts.store');

        Route::get('/{purchase}/edit', 'edit')->name('edit');
        Route::put('/{purchase}', 'update')->name('update');
        Route::get('/{purchase}', 'show')->name('show');
        Route::patch('/{purchase}/deactivate', 'deactivate')->name('deactivate');
        Route::patch('/{purchase}/confirmar', 'confirm')->name('confirm');
        Route::patch('/{purchase}/cancelar', 'cancel')->name('cancel');
    });

#Vendas
Route::prefix('vendas')
    ->name('sales.')
    ->controller(SaleController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{sale}/edit', 'edit')->name('edit');
        Route::put('/{sale}', 'update')->name('update');
        Route::get('/{sale}', 'show')->name('show');
        Route::patch('/{sale}/cancelar', 'cancel')->name('cancel');
        Route::patch('/{sale}/concluir', 'complete')->name('complete');
    });



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
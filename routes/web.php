
<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\FinancialAccountController;
use App\Http\Controllers\CashFlowController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportExportController;

/*
|--------------------------------------------------------------------------
| AUTENTICAÇÃO
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| ROTAS PROTEGIDAS
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'active'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | INÍCIO E DASHBOARD
    |--------------------------------------------------------------------------
    */

    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | USUÁRIOS
    |--------------------------------------------------------------------------
    */

    Route::prefix('usuarios')
        ->name('users.')
        ->middleware('can-manage-users')
        ->controller(UserController::class)
        ->group(function () {

            Route::get('/', 'index')->name('index');

            Route::get('/criar', 'create')->name('create');

            Route::post('/', 'store')->name('store');

            Route::get('/{user}/editar', 'edit')->name('edit');

            Route::put('/{user}', 'update')->name('update');

            Route::get('/{user}', 'show')->name('show');

            Route::patch('/{user}/status', 'toggleStatus')
                ->name('toggle-status');
        });


    /*
    |--------------------------------------------------------------------------
    | AUDITORIA
    |--------------------------------------------------------------------------
    */

    Route::prefix('auditoria')
        ->name('audit-logs.')
        ->middleware('can-manage-users')
        ->controller(AuditLogController::class)
        ->group(function () {

            Route::get('/', 'index')->name('index');

            Route::get('/{auditLog}', 'show')->name('show');
        });


    /*
    |--------------------------------------------------------------------------
    | ANEXOS E COMPROVANTES
    |--------------------------------------------------------------------------
    */

    Route::prefix('anexos')
        ->name('attachments.')
        ->controller(AttachmentController::class)
        ->group(function () {

            Route::get('/{attachment}/visualizar', 'preview')
                ->name('preview');

            Route::get('/{attachment}/baixar', 'download')
                ->name('download');
        });

    /*
    |--------------------------------------------------------------------------
    | RELATÓRIOS
    |--------------------------------------------------------------------------
    */  
    
    Route::prefix('relatorios')
    ->name('reports.')
    ->middleware('permission:modules.view')
    ->group(function () {
        Route::get('/', [ReportController::class, 'index'])
            ->name('index');

        Route::get('/vendas', [ReportController::class, 'sales'])
            ->name('sales.index');

        Route::get('/compras', [ReportController::class, 'purchases'])
            ->name('purchases.index');

        Route::get('/estoque', [ReportController::class, 'stock'])
            ->name('stock.index');

        Route::get('/financeiro', [ReportController::class, 'financial'])
            ->name('financial.index');

        Route::get('/vendas/exportar/excel', [ReportExportController::class, 'sales'])
            ->name('sales.export');

        Route::get('/compras/exportar/excel', [ReportExportController::class, 'purchases'])
            ->name('purchases.export');

        Route::get('/estoque/exportar/excel', [ReportExportController::class, 'stock'])
            ->name('stock.export');

        Route::get('/financeiro/exportar/excel', [ReportExportController::class, 'financial'])
            ->name('financial.export');

        Route::get('/vendas/pdf', [ReportExportController::class, 'salesPdf'])
            ->name('sales.pdf');

        Route::get('/compras/pdf', [ReportExportController::class, 'purchasesPdf'])
            ->name('purchases.pdf');

        Route::get('/estoque/pdf', [ReportExportController::class, 'stockPdf'])
            ->name('stock.pdf');
        
        Route::get('/financeiro/pdf', [ReportExportController::class, 'financialPdf'])
            ->name('financial.pdf');
    });


    /*
    |--------------------------------------------------------------------------
    | PRODUTOS
    |--------------------------------------------------------------------------
    */

    Route::prefix('produtos')
        ->name('products.')
        ->controller(ProductController::class)
        ->group(function () {

            // Visualização
            Route::middleware('permission:products.view')->group(function () {
                Route::get('/', 'index')->name('index');

                Route::get('/{product}', 'show')->name('show');
            });

            // Cadastro
            Route::middleware('permission:products.create')->group(function () {
                Route::get('/create', 'create')->name('create');

                Route::post('/', 'store')->name('store');
            });

            // Edição
            Route::middleware('permission:products.edit')->group(function () {
                Route::get('/{product}/edit', 'edit')->name('edit');

                Route::put('/{product}', 'update')->name('update');
            });

            // Ativar e desativar
            Route::middleware('permission:products.deactivate')->group(function () {
                Route::patch('/{product}/status', 'toggleStatus')
                    ->name('toggle-status');
            });
        });


    /*
    |--------------------------------------------------------------------------
    | CATEGORIAS
    |--------------------------------------------------------------------------
    */

    Route::prefix('categorias')
        ->name('categories.')
        ->controller(CategoryController::class)
        ->group(function () {

            Route::middleware('permission:categories.view')->group(function () {
                Route::get('/', 'index')->name('index');

                Route::get('/{category}', 'show')->name('show');
            });

            Route::middleware('permission:categories.create')->group(function () {
                Route::get('/create', 'create')->name('create');

                Route::post('/', 'store')->name('store');
            });

            Route::middleware('permission:categories.edit')->group(function () {
                Route::get('/{category}/edit', 'edit')->name('edit');

                Route::put('/{category}', 'update')->name('update');
            });

            Route::delete('/{category}', 'destroy')
                ->middleware('permission:categories.deactivate')
                ->name('destroy');
        });


    /*
    |--------------------------------------------------------------------------
    | UNIDADES
    |--------------------------------------------------------------------------
    */

    Route::prefix('unidades')
        ->name('units.')
        ->controller(UnitController::class)
        ->group(function () {

            Route::middleware('permission:units.view')->group(function () {
                Route::get('/', 'index')->name('index');

                Route::get('/{unit}', 'show')->name('show');
            });

            Route::middleware('permission:units.create')->group(function () {
                Route::get('/create', 'create')->name('create');

                Route::post('/', 'store')->name('store');
            });

            Route::middleware('permission:units.edit')->group(function () {
                Route::get('/{unit}/edit', 'edit')->name('edit');

                Route::put('/{unit}', 'update')->name('update');
            });

            Route::delete('/{unit}', 'destroy')
                ->middleware('permission:units.deactivate')
                ->name('destroy');
        });


    /*
    |--------------------------------------------------------------------------
    | CLIENTES
    |--------------------------------------------------------------------------
    */

    Route::prefix('clientes')
        ->name('customers.')
        ->controller(CustomerController::class)
        ->group(function () {

            Route::middleware('permission:customers.view')->group(function () {
                Route::get('/', 'index')->name('index');

                Route::get('/{customer}', 'show')->name('show');
            });

            Route::middleware('permission:customers.create')->group(function () {
                Route::get('/create', 'create')->name('create');

                Route::post('/', 'store')->name('store');
            });

            Route::middleware('permission:customers.edit')->group(function () {
                Route::get('/{customer}/edit', 'edit')->name('edit');

                Route::put('/{customer}', 'update')->name('update');
            });

            Route::delete('/{customer}', 'destroy')
                ->middleware('permission:customers.deactivate')
                ->name('destroy');
        });


    /*
    |--------------------------------------------------------------------------
    | FORNECEDORES
    |--------------------------------------------------------------------------
    */

    Route::prefix('fornecedores')
        ->name('suppliers.')
        ->controller(SupplierController::class)
        ->group(function () {

            Route::middleware('permission:suppliers.view')->group(function () {
                Route::get('/', 'index')->name('index');

                Route::get('/{supplier}', 'show')->name('show');
            });

            Route::middleware('permission:suppliers.create')->group(function () {
                Route::get('/create', 'create')->name('create');

                Route::post('/', 'store')->name('store');
            });

            Route::middleware('permission:suppliers.edit')->group(function () {
                Route::get('/{supplier}/edit', 'edit')->name('edit');

                Route::put('/{supplier}', 'update')->name('update');
            });

            Route::middleware('permission:suppliers.deactivate')->group(function () {
                Route::patch('/{supplier}/status', 'toggleStatus')
                    ->name('toggle-status');
            });
        });


    /*
    |--------------------------------------------------------------------------
    | ESTOQUE
    |--------------------------------------------------------------------------
    */

    Route::prefix('estoque')
        ->name('stock.')
        ->controller(StockController::class)
        ->group(function () {

            Route::get('/', 'index')
                ->middleware('permission:stock.view')
                ->name('index');

            Route::get('/entrada', 'create')
                ->middleware('permission:stock.entry')
                ->name('create');

            Route::post('/entrada', 'store')
                ->middleware('permission:stock.entry')
                ->name('store');

            Route::get('/historico', 'history')
                ->middleware('permission:stock.history')
                ->name('history');
        });


    /*
    |--------------------------------------------------------------------------
    | COMPRAS
    |--------------------------------------------------------------------------
    */

    Route::prefix('compras')
        ->name('purchases.')
        ->controller(PurchaseController::class)
        ->group(function () {

            Route::middleware('permission:purchases.view')->group(function () {
                Route::get('/', 'index')->name('index');

                Route::get('/{purchase}', 'show')->name('show');
            });

            Route::middleware('permission:purchases.create')->group(function () {
                Route::get('/criar', 'create')->name('create');

                Route::post('/', 'store')->name('store');
            });

            Route::middleware('permission:purchases.edit')->group(function () {
                Route::get('/{purchase}/editar', 'edit')->name('edit');

                Route::put('/{purchase}', 'update')->name('update');
            });

            Route::patch('/{purchase}/desativar', 'deactivate')
                ->middleware('permission:purchases.deactivate')
                ->name('deactivate');

            Route::post('/{purchase}/confirmar', 'confirm')
                ->middleware('permission:purchases.confirm')
                ->name('confirm');

            Route::post('/{purchase}/cancelar', 'cancel')
                ->middleware('permission:purchases.cancel')
                ->name('cancel');
        });


    /*
    |--------------------------------------------------------------------------
    | RECEBIMENTO DE MERCADORIAS
    |--------------------------------------------------------------------------
    */

    Route::prefix('recebimentos')
        ->name('goods-receipts.')
        ->controller(GoodsReceiptController::class)
        ->middleware('permission:goods-receipts.create')
        ->group(function () {

            Route::get('/criar/{purchase}', 'create')->name('create');

            Route::post('/{purchase}', 'store')->name('store');
        });


    /*
    |--------------------------------------------------------------------------
    | VENDAS
    |--------------------------------------------------------------------------
    */

    Route::prefix('vendas')
        ->name('sales.')
        ->controller(SaleController::class)
        ->group(function () {

            Route::middleware('permission:sales.view')->group(function () {
                Route::get('/', 'index')->name('index');

                Route::get('/{sale}', 'show')->name('show');
            });

            Route::middleware('permission:sales.create')->group(function () {
                Route::get('/criar', 'create')->name('create');

                Route::post('/', 'store')->name('store');
            });

            Route::middleware('permission:sales.edit')->group(function () {
                Route::get('/{sale}/editar', 'edit')->name('edit');

                Route::put('/{sale}', 'update')->name('update');
            });

            Route::post('/{sale}/concluir', 'complete')
                ->middleware('permission:sales.complete')
                ->name('complete');

            Route::post('/{sale}/cancelar', 'cancel')
                ->middleware('permission:sales.cancel')
                ->name('cancel');
        });


    /*
    |--------------------------------------------------------------------------
    | CONTAS FINANCEIRAS
    |--------------------------------------------------------------------------
    */

    Route::prefix('financeiro/contas')
        ->name('financial-accounts.')
        ->controller(FinancialAccountController::class)
        ->group(function () {

            Route::middleware('permission:financial-accounts.view')->group(function () {
                Route::get('/', 'index')->name('index');

                Route::get('/{financialAccount}', 'show')->name('show');
            });

            Route::middleware('permission:financial-accounts.create')->group(function () {
                Route::get('/criar', 'create')->name('create');

                Route::post('/', 'store')->name('store');
            });

            Route::post('/{financialAccount}/transacoes', 'storeTransaction')
                ->middleware('permission:financial-accounts.transact')
                ->name('transactions.store');
        });


    /*
    |--------------------------------------------------------------------------
    | FLUXO DE CAIXA
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/financeiro/fluxo-de-caixa',
        [CashFlowController::class, 'index']
    )
        ->middleware('permission:cash-flow.view')
        ->name('financial.cash-flow.index');


    /*
    |--------------------------------------------------------------------------
    | MÓDULOS DEMONSTRATIVOS
    |--------------------------------------------------------------------------
    */

    Route::get('/modulos/{modulo}', function (string $modulo) {

        $modules = [
            'cadastros' => [
                'title' => 'Cadastros',
                'description' => 'Gerencie os cadastros do sistema.',
            ],
            'estoque' => [
                'title' => 'Estoque',
                'description' => 'Acompanhe os produtos e movimentações.',
            ],
            'compras' => [
                'title' => 'Compras',
                'description' => 'Gerencie suas compras.',
            ],
            'vendas' => [
                'title' => 'Vendas',
                'description' => 'Gerencie suas vendas.',
            ],
            'financeiro' => [
                'title' => 'Financeiro',
                'description' => 'Acompanhe a gestão financeira.',
            ],
            'caixa' => [
                'title' => 'Caixa',
                'description' => 'Gerencie os fechamentos de caixa.',
            ],
            'relatorios' => [
                'title' => 'Relatórios',
                'description' => 'Consulte os relatórios gerenciais.',
            ],
            'configuracoes' => [
                'title' => 'Configurações',
                'description' => 'Configure o sistema.',
            ],
        ];

        abort_unless(array_key_exists($modulo, $modules), 404);

        return view('modules.placeholder', [
            'module' => $modules[$modulo],
        ]);

    })
        ->middleware('permission:modules.view')
        ->name('modules.show');

});
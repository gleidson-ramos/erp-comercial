<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\FinancialAccount;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Módulos disponíveis para o usuário
        |--------------------------------------------------------------------------
        */

        $moduleDefinitions = [
            [
                'title' => 'Dashboard',
                'description' => 'Indicadores, gráficos e desempenho do negócio.',
                'icon' => 'bi-speedometer2',
                'color' => 'primary',
                'permissions' => ['dashboard.view'],
                'destinations' => [
                    ['permission' => 'dashboard.view', 'route' => 'dashboard'],
                ],
            ],
            [
                'title' => 'Produtos',
                'description' => 'Consulte e gerencie os produtos cadastrados.',
                'icon' => 'bi-box-seam',
                'color' => 'primary',
                'permissions' => ['products.view', 'products.create'],
                'destinations' => [
                    ['permission' => 'products.view', 'route' => 'products.index'],
                    ['permission' => 'products.create', 'route' => 'products.create'],
                ],
            ],
            [
                'title' => 'Clientes',
                'description' => 'Acesse os cadastros e informações dos clientes.',
                'icon' => 'bi-people',
                'color' => 'success',
                'permissions' => ['customers.view', 'customers.create'],
                'destinations' => [
                    ['permission' => 'customers.view', 'route' => 'customers.index'],
                    ['permission' => 'customers.create', 'route' => 'customers.create'],
                ],
            ],
            [
                'title' => 'Fornecedores',
                'description' => 'Consulte e gerencie seus fornecedores.',
                'icon' => 'bi-truck',
                'color' => 'info',
                'permissions' => ['suppliers.view', 'suppliers.create'],
                'destinations' => [
                    ['permission' => 'suppliers.view', 'route' => 'suppliers.index'],
                    ['permission' => 'suppliers.create', 'route' => 'suppliers.create'],
                ],
            ],
            [
                'title' => 'Vendas',
                'description' => 'Registre vendas e acompanhe as operações comerciais.',
                'icon' => 'bi-cart-check',
                'color' => 'success',
                'permissions' => ['sales.view', 'sales.create'],
                'destinations' => [
                    ['permission' => 'sales.view', 'route' => 'sales.index'],
                    ['permission' => 'sales.create', 'route' => 'sales.create'],
                ],
            ],
            [
                'title' => 'Compras',
                'description' => 'Gerencie pedidos de compra e recebimentos.',
                'icon' => 'bi-bag',
                'color' => 'warning',
                'permissions' => ['purchases.view', 'purchases.create'],
                'destinations' => [
                    ['permission' => 'purchases.view', 'route' => 'purchases.index'],
                    ['permission' => 'purchases.create', 'route' => 'purchases.create'],
                ],
            ],
            [
                'title' => 'Estoque',
                'description' => 'Consulte o estoque e registre movimentações.',
                'icon' => 'bi-archive',
                'color' => 'secondary',
                'permissions' => ['stock.view', 'stock.entry', 'stock.history'],
                'destinations' => [
                    ['permission' => 'stock.view', 'route' => 'stock.index'],
                    ['permission' => 'stock.entry', 'route' => 'stock.entry.create'],
                    ['permission' => 'stock.history', 'route' => 'stock.history'],
                ],
            ],
            [
                'title' => 'Financeiro',
                'description' => 'Acompanhe contas a pagar e a receber.',
                'icon' => 'bi-cash-stack',
                'color' => 'success',
                'permissions' => [
                    'financial-accounts.view',
                    'financial-accounts.create',
                ],
                'destinations' => [
                    ['permission' => 'financial-accounts.view', 'route' => 'financial-accounts.index'],
                    ['permission' => 'financial-accounts.create', 'route' => 'financial-accounts.create'],
                ],
            ],
            [
                'title' => 'Fluxo de caixa',
                'description' => 'Consulte entradas e saídas financeiras.',
                'icon' => 'bi-graph-up-arrow',
                'color' => 'info',
                'permissions' => ['cash-flow.view'],
                'destinations' => [
                    ['permission' => 'cash-flow.view', 'route' => 'financial.cash-flow.index'],
                ],
            ],
            [
                'title' => 'Categorias',
                'description' => 'Organize os produtos por categorias.',
                'icon' => 'bi-tags',
                'color' => 'secondary',
                'permissions' => ['categories.view', 'categories.create'],
                'destinations' => [
                    ['permission' => 'categories.view', 'route' => 'categories.index'],
                    ['permission' => 'categories.create', 'route' => 'categories.create'],
                ],
            ],
            [
                'title' => 'Unidades',
                'description' => 'Gerencie as unidades de medida utilizadas.',
                'icon' => 'bi-rulers',
                'color' => 'secondary',
                'permissions' => ['units.view', 'units.create'],
                'destinations' => [
                    ['permission' => 'units.view', 'route' => 'units.index'],
                    ['permission' => 'units.create', 'route' => 'units.create'],
                ],
            ],
            [
                'title' => 'Auditoria',
                'description' => 'Consulte o histórico de alterações do sistema.',
                'icon' => 'bi-clock-history',
                'color' => 'dark',
                'permissions' => ['audit-logs.view'],
                'destinations' => [
                    ['permission' => 'audit-logs.view', 'route' => 'audit-logs.index'],
                ],
            ],
            [
                'title' => 'Usuários',
                'description' => 'Gerencie os usuários e seus acessos.',
                'icon' => 'bi-person-gear',
                'color' => 'dark',
                'permissions' => [],
                'special_permission' => 'can_manage_users',
                'destinations' => [
                    ['special_permission' => 'can_manage_users', 'route' => 'users.index'],
                ],
            ],
        ];

        $modules = collect($moduleDefinitions)
            ->filter(function ($module) use ($user) {
                if (($module['special_permission'] ?? null) === 'can_manage_users') {
                    return $user->canManageUsers();
                }

                return $user->hasAnyPermission($module['permissions']);
            })
            ->map(function ($module) use ($user) {
                $destination = collect($module['destinations'])
                    ->first(function ($item) use ($user) {
                        if (isset($item['special_permission'])) {
                            return $user->canManageUsers()
                                && Route::has($item['route']);
                        }

                        return $user->hasPermission($item['permission'])
                            && Route::has($item['route']);
                    });

                $module['url'] = $destination
                    ? route($destination['route'])
                    : route('home');

                return $module;
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Resumo operacional
        |--------------------------------------------------------------------------
        |
        | Cada indicador só é consultado se o usuário tiver permissão
        | para acessar o respectivo módulo.
        |
        */

        $summary = [];

        if ($user->hasPermission('products.view')) {
            $summary['products'] = [
                'label' => 'Produtos cadastrados',
                'value' => Product::count(),
                'icon' => 'bi-box-seam',
                'color' => 'primary',
            ];
        }

        if ($user->hasPermission('customers.view')) {
            $summary['customers'] = [
                'label' => 'Clientes cadastrados',
                'value' => Customer::count(),
                'icon' => 'bi-people',
                'color' => 'success',
            ];
        }

        if ($user->hasPermission('sales.view')) {
            $summary['sales'] = [
                'label' => 'Vendas neste mês',
                'value' => Sale::query()
                    ->where('status', Sale::STATUS_COMPLETED)
                    ->whereBetween('sale_date', [
                        now()->startOfMonth()->toDateString(),
                        now()->endOfMonth()->toDateString(),
                    ])
                    ->count(),
                'icon' => 'bi-cart-check',
                'color' => 'info',
            ];
        }

        if ($user->hasPermission('purchases.view')) {
            $summary['purchases'] = [
                'label' => 'Compras neste mês',
                'value' => Purchase::query()
                    ->whereIn('status', [
                        Purchase::STATUS_CONFIRMED,
                        Purchase::STATUS_PARTIAL,
                        Purchase::STATUS_RECEIVED,
                    ])
                    ->whereBetween('purchase_date', [
                        now()->startOfMonth()->toDateString(),
                        now()->endOfMonth()->toDateString(),
                    ])
                    ->count(),
                'icon' => 'bi-bag',
                'color' => 'warning',
            ];
        }

        if ($user->hasPermission('financial-accounts.view')) {
            $summary['receivable'] = [
                'label' => 'Contas a receber em aberto',
                'value' => FinancialAccount::query()
                    ->where('type', FinancialAccount::TYPE_RECEIVABLE)
                    ->whereIn('status', [
                        FinancialAccount::STATUS_OPEN,
                        FinancialAccount::STATUS_PARTIAL,
                    ])
                    ->count(),
                'icon' => 'bi-wallet2',
                'color' => 'success',
            ];

            $summary['payable'] = [
                'label' => 'Contas a pagar em aberto',
                'value' => FinancialAccount::query()
                    ->where('type', FinancialAccount::TYPE_PAYABLE)
                    ->whereIn('status', [
                        FinancialAccount::STATUS_OPEN,
                        FinancialAccount::STATUS_PARTIAL,
                    ])
                    ->count(),
                'icon' => 'bi-credit-card',
                'color' => 'danger',
            ];
        }

        return view('home', [
            'modules' => $modules,
            'summary' => $summary,
        ]);
    }
}
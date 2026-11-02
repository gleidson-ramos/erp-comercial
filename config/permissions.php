
<?php

return [

    'dashboard' => [
        'label' => 'Dashboard',
        'permissions' => [
            'dashboard.view' => 'Visualizar dashboard',
        ],
    ],

    'products' => [
        'label' => 'Produtos',
        'permissions' => [
            'products.view' => 'Visualizar produtos',
            'products.create' => 'Cadastrar produtos',
            'products.edit' => 'Editar produtos',
            'products.deactivate' => 'Ativar e desativar produtos',
        ],
    ],

    'categories' => [
        'label' => 'Categorias',
        'permissions' => [
            'categories.view' => 'Visualizar categorias',
            'categories.create' => 'Cadastrar categorias',
            'categories.edit' => 'Editar categorias',
            'categories.deactivate' => 'Ativar e desativar categorias',
        ],
    ],

    'units' => [
        'label' => 'Unidades',
        'permissions' => [
            'units.view' => 'Visualizar unidades',
            'units.create' => 'Cadastrar unidades',
            'units.edit' => 'Editar unidades',
            'units.deactivate' => 'Ativar e desativar unidades',
        ],
    ],

    'customers' => [
        'label' => 'Clientes',
        'permissions' => [
            'customers.view' => 'Visualizar clientes',
            'customers.create' => 'Cadastrar clientes',
            'customers.edit' => 'Editar clientes',
            'customers.deactivate' => 'Ativar e desativar clientes',
        ],
    ],

    'suppliers' => [
        'label' => 'Fornecedores',
        'permissions' => [
            'suppliers.view' => 'Visualizar fornecedores',
            'suppliers.create' => 'Cadastrar fornecedores',
            'suppliers.edit' => 'Editar fornecedores',
            'suppliers.deactivate' => 'Ativar e desativar fornecedores',
        ],
    ],

    'stock' => [
        'label' => 'Estoque',
        'permissions' => [
            'stock.view' => 'Visualizar estoque',
            'stock.entry' => 'Registrar entradas de estoque',
            'stock.history' => 'Consultar histórico de movimentações',
        ],
    ],

    'purchases' => [
        'label' => 'Compras',
        'permissions' => [
            'purchases.view' => 'Visualizar compras',
            'purchases.create' => 'Cadastrar compras',
            'purchases.edit' => 'Editar compras',
            'purchases.deactivate' => 'Desativar compras',
            'purchases.confirm' => 'Confirmar compras',
            'purchases.cancel' => 'Cancelar compras',
        ],
    ],

    'goods-receipts' => [
        'label' => 'Recebimento de mercadorias',
        'permissions' => [
            'goods-receipts.create' => 'Registrar recebimentos',
        ],
    ],

    'sales' => [
        'label' => 'Vendas',
        'permissions' => [
            'sales.view' => 'Visualizar vendas',
            'sales.create' => 'Cadastrar vendas',
            'sales.edit' => 'Editar vendas',
            'sales.complete' => 'Concluir vendas',
            'sales.cancel' => 'Cancelar vendas',
        ],
    ],

    'financial-accounts' => [
        'label' => 'Contas financeiras',
        'permissions' => [
            'financial-accounts.view' => 'Visualizar contas',
            'financial-accounts.create' => 'Cadastrar contas',
            'financial-accounts.transact' => 'Registrar pagamentos e recebimentos',
        ],
    ],

    'cash-flow' => [
        'label' => 'Fluxo de caixa',
        'permissions' => [
            'cash-flow.view' => 'Visualizar fluxo de caixa',
        ],
    ],

    'audit-logs' => [
        'label' => 'Auditoria',
        'permissions' => [
            'audit-logs.view' => 'Consultar auditoria',
        ],
    ],

    'modules' => [
        'label' => 'Módulos demonstrativos',
        'permissions' => [
            'modules.view' => 'Visualizar módulos demonstrativos',
        ],
    ],

];
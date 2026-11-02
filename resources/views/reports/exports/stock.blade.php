
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Estoque</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #333;
        }

        h1 { font-size: 20px; margin-bottom: 5px; }
        h2 { font-size: 14px; margin-top: 22px; }

        .header {
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .muted { color: #666; }

        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .summary td {
            width: 33.33%;
            border: 1px solid #ddd;
            padding: 9px;
        }

        .summary strong {
            display: block;
            font-size: 14px;
            margin-top: 4px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .data th, .data td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        .data th { background: #eee; }

        .right { text-align: right !important; }

        .footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 8px;
        }

        .page-break { page-break-before: always; }
    </style>
</head>

<body>
    <div class="header">
        <h1>Relatório de Estoque</h1>

        <p class="muted">
            Gerado em {{ now()->format('d/m/Y H:i') }}
        </p>

        <p>
            Produto:
            {{ $products->firstWhere('id', $filters['product_id'] ?? null)?->name ?? 'Todos' }}
            |
            Período das movimentações:
            {{ !empty($filters['date_from']) ? \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y') : 'Não informado' }}
            até
            {{ !empty($filters['date_to']) ? \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y') : 'Não informado' }}
            |
            Tipo:
            {{ ($filters['type'] ?? '') === 'IN' ? 'Entrada' : (($filters['type'] ?? '') === 'OUT' ? 'Saída' : 'Todos') }}
        </p>
    </div>

    <table class="summary">
        <tr>
            <td>
                Produtos controlados
                <strong>{{ $summary['products_count'] ?? 0 }}</strong>
            </td>

            <td>
                Quantidade total em estoque
                <strong>
                    {{ number_format((float) ($summary['total_quantity'] ?? 0), 3, ',', '.') }}
                </strong>
            </td>

            <td>
                Produtos abaixo do mínimo
                <strong>{{ $summary['low_stock_count'] ?? 0 }}</strong>
            </td>
        </tr>
    </table>

    <h2>Posição atual do estoque</h2>

    <table class="data">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Unidade</th>
                <th class="right">Quantidade atual</th>
                <th class="right">Estoque mínimo</th>
            </tr>
        </thead>

        <tbody>
            @forelse($stockPosition as $stock)
                <tr>
                    <td>{{ $stock->product->name ?? '-' }}</td>

                    <td>
                        {{ $stock->product->unit->abbreviation
                            ?? $stock->product->unit->name
                            ?? '-' }}
                    </td>

                    <td class="right">
                        {{ number_format((float) $stock->quantity, 3, ',', '.') }}
                    </td>

                    <td class="right">
                        {{ number_format((float) ($stock->product->minimum_stock ?? 0), 3, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Nenhum registro de estoque encontrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Produtos abaixo do estoque mínimo</h2>

    <table class="data">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Unidade</th>
                <th class="right">Quantidade atual</th>
                <th class="right">Estoque mínimo</th>
                <th class="right">Quantidade para reposição</th>
            </tr>
        </thead>

        <tbody>
            @forelse($lowStockProducts as $stock)
                <tr>
                    <td>{{ $stock->product->name ?? '-' }}</td>

                    <td>
                        {{ $stock->product->unit->abbreviation
                            ?? $stock->product->unit->name
                            ?? '-' }}
                    </td>

                    <td class="right">
                        {{ number_format((float) $stock->quantity, 3, ',', '.') }}
                    </td>

                    <td class="right">
                        {{ number_format((float) ($stock->product->minimum_stock ?? 0), 3, ',', '.') }}
                    </td>

                    <td class="right">
                        {{ number_format(
                            max(0, (float) ($stock->product->minimum_stock ?? 0) - (float) $stock->quantity),
                            3,
                            ',',
                            '.'
                        ) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Nenhum produto abaixo do estoque mínimo.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>

    <h2>Resumo das movimentações</h2>

    <table class="summary">
        <tr>
            <td>
                Total de movimentações
                <strong>{{ $movementSummary['total_movements'] ?? 0 }}</strong>
            </td>

            <td>
                Entradas
                <strong>{{ $movementSummary['total_entries'] ?? 0 }}</strong>
            </td>

            <td>
                Saídas
                <strong>{{ $movementSummary['total_exits'] ?? 0 }}</strong>
            </td>
        </tr>
    </table>

    <h2>Histórico de movimentações</h2>

    <table class="data">
        <thead>
            <tr>
                <th>Data</th>
                <th>Produto</th>
                <th>Tipo</th>
                <th class="right">Quantidade</th>
                <th class="right">Custo unitário</th>
                <th class="right">Saldo após</th>
                <th>Motivo</th>
                <th>Observações</th>
            </tr>
        </thead>

        <tbody>
            @forelse($movements as $movement)
                <tr>
                    <td>{{ $movement->moved_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td>{{ $movement->product->name ?? '-' }}</td>

                    <td>
                        {{ $movement->type === 'IN' ? 'Entrada' : 'Saída' }}
                    </td>

                    <td class="right">
                        {{ number_format((float) $movement->quantity, 3, ',', '.') }}
                    </td>

                    <td class="right">
                        R$ {{ number_format((float) $movement->unit_cost, 2, ',', '.') }}
                    </td>

                    <td class="right">
                        {{ number_format((float) $movement->balance_after, 3, ',', '.') }}
                    </td>

                    <td>{{ $movement->reason ?? '-' }}</td>
                    <td>{{ $movement->notes ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Nenhuma movimentação encontrada.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        ERP Comercial — Relatório gerado automaticamente pelo sistema.
    </div>
</body>
</html>
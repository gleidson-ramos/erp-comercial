<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <title>Relatório de Vendas</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #333;
        }

        h1 {
            font-size: 20px;
            margin-bottom: 5px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 20px;
        }

        .header {
            border-bottom: 2px solid #333;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .summary {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .summary td {
            border: 1px solid #ddd;
            padding: 10px;
            width: 25%;
        }

        .summary strong {
            display: block;
            font-size: 14px;
            margin-top: 5px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
        }

        table.data th,
        table.data td {
            border: 1px solid #ddd;
            padding: 7px;
            text-align: left;
        }

        table.data th {
            background: #f0f0f0;
        }

        .text-right {
            text-align: right !important;
        }

        .footer {
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 8px;
            font-size: 9px;
            color: #666;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>Relatório de Vendas</h1>

        <div class="subtitle">
            Período:
            {{ !empty($filters['date_from']) ? \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y') : 'Não informado' }}
            até
            {{ !empty($filters['date_to']) ? \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y') : 'Não informado' }}
        </div>

        <div>
            Gerado em: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <table class="summary">
        <tr>
            <td>
                Total de vendas
                <strong>
                    {{ $summary['total_sales'] ?? $summary['count'] ?? 0 }}
                </strong>
            </td>

            <td>
                Faturamento
                <strong>
                    R$ {{ number_format((float) ($summary['total_revenue'] ?? $summary['revenue'] ?? $summary['total'] ?? 0), 2, ',', '.') }}
                </strong>
            </td>

            <td>
                Descontos
                <strong>
                    R$ {{ number_format((float) ($summary['total_discount'] ?? $summary['discount'] ?? 0), 2, ',', '.') }}
                </strong>
            </td>

            <td>
                Ticket médio
                <strong>
                    R$ {{ number_format((float) ($summary['average_ticket'] ?? $summary['average'] ?? 0), 2, ',', '.') }}
                </strong>
            </td>
        </tr>
    </table>

    <h3>Vendas realizadas</h3>

    <table class="data">
        <thead>
            <tr>
                <th>Número</th>
                <th>Data</th>
                <th>Cliente</th>
                <th>Status</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">Desconto</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>

        <tbody>
            @forelse($sales as $sale)
                <tr>
                    <td>{{ $sale->number }}</td>

                    <td>
                        {{ $sale->sale_date
                            ? \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y')
                            : '-' }}
                    </td>

                    <td>{{ $sale->customer->name ?? 'Consumidor não identificado' }}</td>

                    <td>{{ $sale->status }}</td>

                    <td class="text-right">
                        R$ {{ number_format((float) $sale->subtotal, 2, ',', '.') }}
                    </td>

                    <td class="text-right">
                        R$ {{ number_format((float) $sale->discount, 2, ',', '.') }}
                    </td>

                    <td class="text-right">
                        R$ {{ number_format((float) $sale->total, 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        Nenhuma venda encontrada para os filtros informados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        ERP Comercial — Relatório gerado automaticamente pelo sistema.
    </div>

</body>
</html>
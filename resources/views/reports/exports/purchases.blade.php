
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Compras</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #333;
        }

        h1 { font-size: 20px; }

        .header {
            border-bottom: 2px solid #333;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .subtitle, .footer {
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 7px;
            text-align: left;
        }

        th { background: #f0f0f0; }

        .right { text-align: right; }

        .footer {
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 8px;
            font-size: 9px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Relatório de Compras</h1>

        <p class="subtitle">
            Período:
            {{ $filters['date_from'] ?? 'Não informado' }}
            até
            {{ $filters['date_to'] ?? 'Não informado' }}
        </p>

        <p>Gerado em: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <h3>Compras realizadas</h3>

    <table>
        <thead>
            <tr>
                <th>Número</th>
                <th>Fornecedor</th>
                <th>Data</th>
                <th>Previsão de entrega</th>
                <th>Status</th>
                <th class="right">Total</th>
            </tr>
        </thead>

        <tbody>
            @forelse($purchases as $purchase)
                <tr>
                    <td>{{ $purchase->number ?? $purchase->id }}</td>

                    <td>
                        {{ $purchase->supplier->name ?? '-' }}
                    </td>

                    <td>
                        {{ $purchase->purchase_date
                            ? \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y')
                            : ($purchase->created_at?->format('d/m/Y') ?? '-') }}
                    </td>

                    <td>
                        {{ $purchase->expected_delivery_date
                            ? \Carbon\Carbon::parse($purchase->expected_delivery_date)->format('d/m/Y')
                            : '-' }}
                    </td>

                    <td>{{ $purchase->status }}</td>

                    <td class="right">
                        R$ {{ number_format((float) $purchase->total, 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        Nenhuma compra encontrada.
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
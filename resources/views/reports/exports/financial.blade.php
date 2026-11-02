
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório Financeiro</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #333;
        }

        h1 { font-size: 20px; margin-bottom: 5px; }
        h2 { font-size: 14px; margin-top: 20px; }

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
            width: 25%;
            border: 1px solid #ddd;
            padding: 9px;
        }

        .summary strong {
            display: block;
            font-size: 13px;
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
    </style>
</head>

<body>
    <div class="header">
        <h1>Relatório Financeiro</h1>

        <p class="muted">
            Gerado em {{ now()->format('d/m/Y H:i') }}
        </p>

        <p>
            Vencimentos:
            {{ !empty($filters['date_from']) ? \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y') : 'Não informado' }}
            até
            {{ !empty($filters['date_to']) ? \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y') : 'Não informado' }}
            |
            Tipo:
            {{ ($filters['type'] ?? '') === 'RECEBER' ? 'A receber' : (($filters['type'] ?? '') === 'PAGAR' ? 'A pagar' : 'Todos') }}
            |
            Status:
            {{ $filters['status'] ?? 'Todos' }}
        </p>
    </div>

    <h2>Resumo financeiro</h2>

    <table class="summary">
        <tr>
            <td>
                Total a receber
                <strong>
                    R$ {{ number_format((float) ($summary['total_receivable'] ?? 0), 2, ',', '.') }}
                </strong>
            </td>

            <td>
                Total a pagar
                <strong>
                    R$ {{ number_format((float) ($summary['total_payable'] ?? 0), 2, ',', '.') }}
                </strong>
            </td>

            <td>
                Valores recebidos
                <strong>
                    R$ {{ number_format((float) ($summary['received'] ?? 0), 2, ',', '.') }}
                </strong>
            </td>

            <td>
                Valores pagos
                <strong>
                    R$ {{ number_format((float) ($summary['paid'] ?? 0), 2, ',', '.') }}
                </strong>
            </td>
        </tr>

        <tr>
            <td>
                Pendente a receber
                <strong>
                    R$ {{ number_format((float) ($summary['pending_receivable'] ?? 0), 2, ',', '.') }}
                </strong>
            </td>

            <td>
                Pendente a pagar
                <strong>
                    R$ {{ number_format((float) ($summary['pending_payable'] ?? 0), 2, ',', '.') }}
                </strong>
            </td>

            <td>
                Recebimentos vencidos
                <strong>
                    R$ {{ number_format((float) ($summary['overdue_receivable'] ?? 0), 2, ',', '.') }}
                </strong>
            </td>

            <td>
                Pagamentos vencidos
                <strong>
                    R$ {{ number_format((float) ($summary['overdue_payable'] ?? 0), 2, ',', '.') }}
                </strong>
            </td>
        </tr>
    </table>

    <h2>Contas financeiras</h2>

    <table class="data">
        <thead>
            <tr>
                <th>Descrição</th>
                <th>Documento</th>
                <th>Tipo</th>
                <th>Cliente / Fornecedor</th>
                <th>Vencimento</th>
                <th class="right">Valor</th>
                <th class="right">Valor pago</th>
                <th class="right">Saldo pendente</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>
            @forelse($accounts as $account)
                @php
                    $paidAmount = (float) ($account->paid_amount ?? 0);
                    $amount = (float) $account->amount;
                    $pendingAmount = max(0, $amount - $paidAmount);

                    $person = $account->type === 'RECEBER'
                        ? ($account->customer->name ?? '-')
                        : ($account->supplier->name ?? '-');
                @endphp

                <tr>
                    <td>{{ $account->description }}</td>
                    <td>{{ $account->document_number ?? '-' }}</td>

                    <td>
                        {{ $account->type === 'RECEBER' ? 'A receber' : 'A pagar' }}
                    </td>

                    <td>{{ $person }}</td>

                    <td>
                        {{ $account->due_date
                            ? \Carbon\Carbon::parse($account->due_date)->format('d/m/Y')
                            : '-' }}
                    </td>

                    <td class="right">
                        R$ {{ number_format($amount, 2, ',', '.') }}
                    </td>

                    <td class="right">
                        R$ {{ number_format($paidAmount, 2, ',', '.') }}
                    </td>

                    <td class="right">
                        R$ {{ number_format($pendingAmount, 2, ',', '.') }}
                    </td>

                    <td>{{ $account->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">
                        Nenhuma conta financeira encontrada para os filtros informados.
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
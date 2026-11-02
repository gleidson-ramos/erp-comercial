<?php

namespace App\Services;

use App\Models\FinancialAccount;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinancialAccountService
{ 
    public function registerTransaction(
        FinancialAccount $account,
        array $data,
        array $files = []
    ): FinancialTransaction {
        return DB::transaction(function () use ($account, $data, $files) {
            // Bloqueia a conta para evitar transações concorrentes.
            $account = FinancialAccount::query()
                ->lockForUpdate()
                ->findOrFail($account->id);

            if (! in_array($account->status, [
                FinancialAccount::STATUS_OPEN,
                FinancialAccount::STATUS_PARTIAL,
            ], true)) {
                throw ValidationException::withMessages([
                    'financial_account' =>
                        $account->status === FinancialAccount::STATUS_CANCELLED
                            ? 'Não é possível movimentar uma conta cancelada.'
                            : (
                                $account->status === FinancialAccount::STATUS_PAID
                                    ? 'Esta conta já está totalmente quitada.'
                                    : 'O status atual não permite movimentações financeiras.'
                            ),
                ]);
            }

            if (! in_array($account->type, [
                FinancialAccount::TYPE_PAYABLE,
                FinancialAccount::TYPE_RECEIVABLE,
            ], true)) {
                throw ValidationException::withMessages([
                    'financial_account' =>
                        'Tipo de conta financeira inválido.',
                ]);
            }

            $amount = $this->toCents($data['amount'] ?? 0);

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'O valor da transação deve ser maior que zero.',
                ]);
            }

            $totalDue = $this->toCents($account->amount)
                - $this->toCents($account->discount)
                + $this->toCents($account->interest);

            if ($totalDue <= 0) {
                throw ValidationException::withMessages([
                    'financial_account' =>
                        'O valor líquido da conta deve ser maior que zero.',
                ]);
            }

            $paid = $this->toCents(
                $account->transactions()->sum('amount')
            );

            $balance = $totalDue - $paid;

            if ($balance <= 0) {
                throw ValidationException::withMessages([
                    'financial_account' =>
                        'Esta conta não possui saldo pendente.',
                ]);
            }

            if ($amount > $balance) {
                throw ValidationException::withMessages([
                    'amount' =>
                        'O valor informado excede o saldo pendente de R$ '
                        . number_format($balance / 100, 2, ',', '.')
                        . '.',
                ]);
            }

            $type = $account->type === FinancialAccount::TYPE_PAYABLE
                ? FinancialTransaction::TYPE_PAYMENT
                : FinancialTransaction::TYPE_RECEIPT;

            $transaction = $account->transactions()->create([
                'type' => $type,
                'transaction_date' => $data['transaction_date'],
                'amount' => number_format($amount / 100, 2, '.', ''),
                'payment_method' => $data['payment_method'],
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Vincula os comprovantes à transação específica.
            if (! empty($files)) {
                app(\App\Services\AttachmentService::class)
                    ->storeMany($transaction, $files);
            }

            $newPaid = $paid + $amount;

            $account->update([
                'status' => $newPaid >= $totalDue
                    ? FinancialAccount::STATUS_PAID
                    : FinancialAccount::STATUS_PARTIAL,
            ]);

            return $transaction->fresh('financialAccount');
        }, 3);
    }

    private function toCents(mixed $value): int
    {
        return (int) round((float) $value * 100);
    }
}
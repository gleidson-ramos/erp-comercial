<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\FinancialAccount;
use App\Models\FinancialTransaction;
use App\Models\Supplier;
use App\Services\FinancialAccountService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FinancialAccountController extends Controller
{
    public function index(Request $request)
    {
        $query = FinancialAccount::query()
            ->with(['customer', 'supplier'])
            ->withSum('transactions', 'amount');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('description', 'ILIKE', "%{$search}%")
                    ->orWhere('document_number', 'ILIKE', "%{$search}%")
                    ->orWhereHas('customer', function ($customer) use ($search) {
                        $customer->where('name', 'ILIKE', "%{$search}%");
                    })
                    ->orWhereHas('supplier', function ($supplier) use ($search) {
                        $supplier->where('name', 'ILIKE', "%{$search}%")
                            ->orWhere('fantasy_name', 'ILIKE', "%{$search}%");
                    });
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('due_from')) {
            $query->whereDate('due_date', '>=', $request->input('due_from'));
        }

        if ($request->filled('due_to')) {
            $query->whereDate('due_date', '<=', $request->input('due_to'));
        }

        $accounts = $query
            ->orderBy('due_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('financial_accounts.index', compact('accounts'));
    }

    public function create(Request $request)
    {
        $customers = Customer::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $suppliers = Supplier::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $type = $request->query(
            'type',
            FinancialAccount::TYPE_PAYABLE
        );

        if (!in_array($type, [
            FinancialAccount::TYPE_PAYABLE,
            FinancialAccount::TYPE_RECEIVABLE,
        ], true)) {
            $type = FinancialAccount::TYPE_PAYABLE;
        }

        return view(
            'financial_accounts.create',
            compact('customers', 'suppliers', 'type')
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => [
                'required',
                Rule::in([
                    FinancialAccount::TYPE_RECEIVABLE,
                    FinancialAccount::TYPE_PAYABLE,
                ]),
            ],

            'customer_id' => [
                'required_if:type,' . FinancialAccount::TYPE_RECEIVABLE,
                'prohibited_if:type,' . FinancialAccount::TYPE_PAYABLE,
                'nullable',
                Rule::exists('customers', 'id')->where('is_active', true),
            ],

            'supplier_id' => [
                'required_if:type,' . FinancialAccount::TYPE_PAYABLE,
                'prohibited_if:type,' . FinancialAccount::TYPE_RECEIVABLE,
                'nullable',
                Rule::exists('suppliers', 'id')->where('is_active', true),
            ],

            'description' => ['required', 'string', 'max:200'],
            'document_number' => ['nullable', 'string', 'max:50'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'discount' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'interest' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $amountInCents = (int) round((float) $validated['amount'] * 100);

        $discountInCents = (int) round(
            (float) ($validated['discount'] ?? 0) * 100
        );

        $interestInCents = (int) round(
            (float) ($validated['interest'] ?? 0) * 100
        );

        if (
            $amountInCents
            - $discountInCents
            + $interestInCents <= 0
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'discount' =>
                        'O valor líquido da conta deve ser maior que zero.',
                ]);
        }

        if ($validated['due_date'] < $validated['issue_date']) {
            return back()
                ->withInput()
                ->withErrors([
                    'due_date' =>
                        'O vencimento não pode ser anterior à emissão.',
                ]);
        }

        $validated['discount'] = $validated['discount'] ?? 0;
        $validated['interest'] = $validated['interest'] ?? 0;
        $validated['status'] = FinancialAccount::STATUS_OPEN;

        $account = FinancialAccount::create($validated);

        return redirect()
            ->route('financial-accounts.show', $account)
            ->with('success', 'Conta financeira cadastrada com sucesso.');
    }

    public function show(FinancialAccount $financialAccount)
    {
        $financialAccount->load([
            'customer',
            'supplier',
            'sale',
            'purchase',
            'transactions' => fn ($query) => $query
                ->with('attachments.uploader')
                ->orderByDesc('transaction_date')
                ->orderByDesc('id'),
        ]);

        /*
         * Responsável pela criação da conta financeira.
         */
        $accountCreator = AuditLog::query()
            ->where(
                'auditable_type',
                $financialAccount->getMorphClass()
            )
            ->where(
                'auditable_id',
                (string) $financialAccount->getKey()
            )
            ->where('event', 'created')
            ->first();

        /*
         * Responsáveis pelo registro de cada transação.
         */
        $transactionCreators = collect();

        if ($financialAccount->transactions->isNotEmpty()) {
            $transactionIds = $financialAccount->transactions
                ->modelKeys();

            $transactionCreators = AuditLog::query()
                ->where(
                    'auditable_type',
                    (new FinancialTransaction())->getMorphClass()
                )
                ->whereIn(
                    'auditable_id',
                    array_map('strval', $transactionIds)
                )
                ->where('event', 'created')
                ->get()
                ->keyBy('auditable_id');
        }

        return view('financial_accounts.show', [
            'account' => $financialAccount,
            'accountCreator' => $accountCreator,
            'transactionCreators' => $transactionCreators,
        ]);
    }

    public function storeTransaction(
        Request $request,
        FinancialAccount $financialAccount,
        FinancialAccountService $service
    ) {
        $validated = $request->validate([
            'transaction_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],

            'payment_method' => [
                'required',
                Rule::in([
                    FinancialTransaction::METHOD_CASH,
                    FinancialTransaction::METHOD_PIX,
                    FinancialTransaction::METHOD_CARD,
                    FinancialTransaction::METHOD_TRANSFER,
                    FinancialTransaction::METHOD_OTHER,
                ]),
            ],

            'attachments' => ['nullable', 'array', 'max:10'],

            'attachments.*' => [
                'file',
                'mimes:pdf,jpg,jpeg,png,webp',
                'max:10240',
            ],

            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $files = $validated['attachments'] ?? [];

        unset($validated['attachments']);

        $service->registerTransaction(
            $financialAccount,
            $validated,
            $files
        );

        return redirect()
            ->route('financial-accounts.show', $financialAccount)
            ->with('success', 'Transação financeira registrada com sucesso.');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Services\GoodsReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GoodsReceiptController extends Controller
{
    public function __construct(
        private readonly GoodsReceiptService $goodsReceiptService
    ) {
    }

    public function create(Purchase $purchase): View|RedirectResponse
    {
        if (! $purchase->canReceive()) {
            return redirect()
                ->route('purchases.show', $purchase)
                ->with(
                    'error',
                    'Esta compra não está disponível para recebimento.'
                );
        }

        $purchase->load('supplier', 'items.product');

        return view('purchases.receipts.create', compact('purchase'));
    }

    public function store(
        Request $request,
        Purchase $purchase
    ): RedirectResponse {
        $data = $request->validate([
            'received_at' => [
                'nullable',
                'date',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.purchase_item_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('purchase_items', 'id'),
            ],

            'items.*.quantity' => [
                'nullable',
                'numeric',
                'gt:0',
                'decimal:0,3',
                'max:99999999999.999',
            ],
        ], [
            'received_at.date' =>
                'Informe uma data e hora de recebimento válida.',

            'notes.max' =>
                'As observações devem ter no máximo 2.000 caracteres.',

            'items.required' =>
                'Informe pelo menos um produto recebido.',

            'items.min' =>
                'Informe pelo menos um produto recebido.',

            'items.*.purchase_item_id.required' =>
                'Não foi possível identificar um dos produtos da compra.',

            'items.*.purchase_item_id.distinct' =>
                'Cada produto deve aparecer apenas uma vez no recebimento.',

            'items.*.quantity.gt' =>
                'As quantidades recebidas devem ser maiores que zero.',

            'items.*.quantity.decimal' =>
                'As quantidades podem ter até três casas decimais.',
        ]);

        // Remove linhas que não foram selecionadas para recebimento.
        $data['items'] = array_values(array_filter(
            $data['items'],
            fn (array $item) =>
                isset($item['quantity']) &&
                $item['quantity'] !== '' &&
                (float) $item['quantity'] > 0
        ));

        if (empty($data['items'])) {
            return back()
                ->withInput()
                ->withErrors([
                    'items' =>
                        'Informe a quantidade recebida de pelo menos um produto.',
                ]);
        }

        $data['purchase_id'] = $purchase->id;

        $this->goodsReceiptService->register($data);

        return redirect()
            ->route('purchases.show', $purchase)
            ->with(
                'success',
                'Recebimento registrado e estoque atualizado com sucesso.'
            );
    }
}
<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Attachment;

class FinancialTransaction extends Model
{
    use Auditable;
    
    public const TYPE_PAYMENT = 'PAGAMENTO';
    public const TYPE_RECEIPT = 'RECEBIMENTO';

    public const METHOD_CASH = 'DINHEIRO';
    public const METHOD_PIX = 'PIX';
    public const METHOD_CARD = 'CARTAO';
    public const METHOD_TRANSFER = 'TRANSFERENCIA';
    public const METHOD_OTHER = 'OUTRO';

    protected $fillable = [
        'financial_account_id',
        'type',
        'transaction_date',
        'amount',
        'payment_method',
        'reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function financialAccount(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class);
    }
}
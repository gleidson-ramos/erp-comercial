<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialAccount extends Model
{
    use Auditable;

    public const TYPE_RECEIVABLE = 'RECEBER';
    public const TYPE_PAYABLE = 'PAGAR';

    public const STATUS_OPEN = 'ABERTA';
    public const STATUS_PARTIAL = 'PARCIAL';
    public const STATUS_PAID = 'PAGA';
    public const STATUS_CANCELLED = 'CANCELADA';

    protected $fillable = [
        'type',
        'customer_id',
        'supplier_id',
        'sale_id',
        'purchase_id',
        'description',
        'document_number',
        'issue_date',
        'due_date',
        'amount',
        'discount',
        'interest',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'amount' => 'decimal:2',
            'discount' => 'decimal:2',
            'interest' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    public function getTotalDueInCentsAttribute(): int
    {
        return (int) round(
            ((float) $this->amount
                - (float) $this->discount
                + (float) $this->interest) * 100
        );
    }

    public function getPaidInCentsAttribute(): int
    {
        return $this->transactions()
            ->sum('amount') !== null
                ? (int) round(
                    (float) $this->transactions()->sum('amount') * 100
                )
                : 0;
    }

    public function getBalanceInCentsAttribute(): int
    {
        return max(
            0,
            $this->total_due_in_cents - $this->paid_in_cents
        );
    }
}
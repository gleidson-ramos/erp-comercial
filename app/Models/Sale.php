<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Attachment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Sale extends Model
{
    use Auditable;
    
    public const STATUS_DRAFT = 'RASCUNHO';
    public const STATUS_COMPLETED = 'CONCLUIDA';
    public const STATUS_CANCELLED = 'CANCELADA';

    protected $fillable = [
        'customer_id',
        'number',
        'sale_date',
        'status',
        'subtotal',
        'discount',
        'total',
        'notes',
        'completed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canBeEdited(): bool
    {
        return $this->isDraft();
    }

    public function canBeCompleted(): bool
    {
        return $this->isDraft() && $this->items()->exists();
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_COMPLETED,
        ], true);
    }

    public function financialAccount()
    {
        return $this->hasOne(
            \App\Models\FinancialAccount::class,
            'sale_id'
        );
    }
}
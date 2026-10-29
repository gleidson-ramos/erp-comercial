<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    public const STATUS_DRAFT = 'RASCUNHO';
    public const STATUS_CONFIRMED = 'CONFIRMADA';
    public const STATUS_PARTIAL = 'PARCIALMENTE_RECEBIDA';
    public const STATUS_RECEIVED = 'RECEBIDA';
    public const STATUS_CANCELLED = 'CANCELADA';

    protected $fillable = [
        'supplier_id',
        'number',
        'purchase_date',
        'expected_delivery_date',
        'status',
        'subtotal',
        'discount',
        'total',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'expected_delivery_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function canReceive(): bool
    {
        return in_array($this->status, [
            self::STATUS_CONFIRMED,
            self::STATUS_PARTIAL,
        ], true);
    }
}
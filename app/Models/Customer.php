<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'person_type',
        'name',
        'document',
        'email',
        'phone',
        'postal_code',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function getFormattedDocumentAttribute(): ?string
    {
        if (!$this->document) {
            return null;
        }

        if ($this->person_type === 'PF' && strlen($this->document) === 11) {
            return preg_replace(
                '/(\d{3})(\d{3})(\d{3})(\d{2})/',
                '$1.$2.$3-$4',
                $this->document
            );
        }

        if ($this->person_type === 'PJ' && strlen($this->document) === 14) {
            return preg_replace(
                '/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/',
                '$1.$2.$3/$4-$5',
                $this->document
            );
        }

        return $this->document;
    }

    public function getFormattedAddressAttribute(): string
    {
        $parts = array_filter([
            $this->street,
            $this->number,
            $this->complement,
            $this->neighborhood,
            $this->city,
            $this->state,
            $this->postal_code,
        ]);

        return implode(', ', $parts);
    }
}
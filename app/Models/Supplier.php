<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'person_type',
        'name',
        'fantasy_name',
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
        if (empty($this->document)) {
            return null;
        }

        $document = preg_replace('/\D/', '', $this->document);

        if (strlen($document) === 11) {
            return preg_replace(
                '/(\d{3})(\d{3})(\d{3})(\d{2})/',
                '$1.$2.$3-$4',
                $document
            );
        }

        if (strlen($document) === 14) {
            return preg_replace(
                '/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/',
                '$1.$2.$3/$4-$5',
                $document
            );
        }

        return $document;
    }

    public function getFormattedAddressAttribute(): ?string
    {
        $address = array_filter([
            $this->street,
            $this->number,
            $this->complement,
            $this->neighborhood,
            $this->city,
            $this->state,
        ], fn ($value) => filled($value));

        return count($address) > 0
            ? implode(', ', $address)
            : null;
    }
}
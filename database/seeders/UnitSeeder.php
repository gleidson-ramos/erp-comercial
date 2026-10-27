<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Unidade', 'abbreviation' => 'UN'],
            ['name' => 'Quilograma', 'abbreviation' => 'KG'],
            ['name' => 'Grama', 'abbreviation' => 'G'],
            ['name' => 'Litro', 'abbreviation' => 'L'],
            ['name' => 'Mililitro', 'abbreviation' => 'ML'],
            ['name' => 'Metro', 'abbreviation' => 'M'],
            ['name' => 'Caixa', 'abbreviation' => 'CX'],
            ['name' => 'Pacote', 'abbreviation' => 'PCT'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(
                ['abbreviation' => $unit['abbreviation']],
                [
                    'name' => $unit['name'],
                    'is_active' => true,
                ]
            );
        }
    }
}
<?php

namespace Database\Seeders;

use App\Models\ShopType;
use Illuminate\Database\Seeder;

class ShopTypeSeeder extends Seeder
{
    /**
     * @var list<string>
     */
    private const TYPES = [
        'Oziq-ovqat',
        'Kiyim-kechak',
        'Poyabzal',
        'Elektronika',
        'Maishiy texnika',
        'Qurilish mollari',
        'Dorixona',
        'Kosmetika',
        'Xo\'jalik mollari',
        'Boshqa',
    ];

    public function run(): void
    {
        foreach (self::TYPES as $name) {
            ShopType::query()->firstOrCreate(['name' => $name], ['is_active' => true]);
        }
    }
}

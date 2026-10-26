<?php

namespace Database\Factories;

use App\Models\FotoProduk;
use App\Models\Produk;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FotoProduk>
 */
class FotoProdukFactory extends Factory
{
    public function definition(): array
    {
        return [
            'produk_id' => Produk::factory(),
            'path' => 'images/produk/'.fake()->unique()->uuid().'.jpg',
            'urutan' => 0,
        ];
    }
}

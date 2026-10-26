<?php

namespace Database\Factories;

use App\Models\Produk;
use App\Models\Varian;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Varian>
 */
class VarianFactory extends Factory
{
    public function definition(): array
    {
        return [
            'produk_id' => Produk::factory(),
            'nama' => 'Toples 250 g',
            'sku' => 'SMP-'.fake()->unique()->numerify('#####'),
            'harga' => 45000,
            'harga_coret' => null,
            'stok' => 20,
            'berat' => 300,
            'aktif' => true,
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(['aktif' => false]);
    }

    public function habis(): static
    {
        return $this->state(['stok' => 0]);
    }
}

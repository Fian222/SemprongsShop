<?php

namespace Database\Factories;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Produk>
 */
class ProdukFactory extends Factory
{
    public function definition(): array
    {
        $nama = 'Semprong '.fake()->unique()->words(2, true);

        return [
            'kategori_id' => Kategori::factory(),
            'nama' => $nama,
            'slug' => Str::slug($nama),
            'deskripsi' => fake()->sentence(),
            'komposisi' => 'Tepung beras, santan, gula, telur',
            'aktif' => true,
            'unggulan' => false,
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(['aktif' => false]);
    }
}

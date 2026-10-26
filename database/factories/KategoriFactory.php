<?php

namespace Database\Factories;

use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Kategori>
 */
class KategoriFactory extends Factory
{
    public function definition(): array
    {
        $nama = 'Semprong '.fake()->unique()->word();

        return ['nama' => $nama, 'slug' => Str::slug($nama), 'urutan' => 0, 'aktif' => true];
    }
}

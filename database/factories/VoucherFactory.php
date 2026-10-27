<?php

namespace Database\Factories;

use App\Enums\TipeVoucher;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Voucher>
 */
class VoucherFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kode' => 'PROMO'.fake()->unique()->numerify('###'),
            'tipe' => TipeVoucher::Nominal,
            'nilai' => 10000,
            'maks_diskon' => null,
            'min_subtotal' => 0,
            'kuota' => null,
            'mulai' => null,
            'berakhir' => null,
            'aktif' => true,
        ];
    }

    public function persen(int $nilai, ?int $maksDiskon = null): static
    {
        return $this->state(['tipe' => TipeVoucher::Persen, 'nilai' => $nilai, 'maks_diskon' => $maksDiskon]);
    }

    public function nominal(int $nilai): static
    {
        return $this->state(['tipe' => TipeVoucher::Nominal, 'nilai' => $nilai]);
    }
}

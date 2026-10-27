<?php

namespace Database\Seeders;

use App\Enums\TipeVoucher;
use App\Models\Voucher;
use Illuminate\Database\Seeder;

// Voucher contoh untuk pengembangan. Aman dijalankan ulang: baris dicocokkan lewat kode.
class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        $vouchers = [
            ['HEMAT10', TipeVoucher::Persen, 10, 25000, 100000, null],
            ['KENALAN', TipeVoucher::Nominal, 15000, null, 0, 100],
        ];

        foreach ($vouchers as [$kode, $tipe, $nilai, $maksDiskon, $minSubtotal, $kuota]) {
            Voucher::updateOrCreate(['kode' => $kode], [
                'tipe' => $tipe,
                'nilai' => $nilai,
                'maks_diskon' => $maksDiskon,
                'min_subtotal' => $minSubtotal,
                'kuota' => $kuota,
            ]);
        }
    }
}

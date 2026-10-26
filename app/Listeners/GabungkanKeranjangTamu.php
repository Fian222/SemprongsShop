<?php

namespace App\Listeners;

use App\Models\Keranjang;
use Illuminate\Auth\Events\Login;

// Saat pelanggan masuk (termasuk sesudah mendaftar), isi keranjang tamu di sesi ini pindah ke keranjang akunnya
class GabungkanKeranjangTamu
{
    public function handle(Login $event): void
    {
        if ($event->guard !== 'web') {
            return;
        }

        $token = session()->pull(Keranjang::KUNCI_SESI);
        $tamu = $token ? Keranjang::firstWhere('token', $token) : null;

        if ($tamu) {
            Keranjang::firstOrCreate(['user_id' => $event->user->id])->gabungkan($tamu);
        }
    }
}

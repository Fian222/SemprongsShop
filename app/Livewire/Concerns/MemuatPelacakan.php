<?php

namespace App\Livewire\Concerns;

use App\Pengiriman\PelacakanGagal;
use App\Pengiriman\ShippingProvider;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;

// Riwayat pelacakan paket untuk komponen yang punya properti $pesanan; dipakai bersama <x-pelacakan>
trait MemuatPelacakan
{
    // hasil Pelacakan::keArray(); null selama belum dimuat
    #[Locked]
    public ?array $pelacakan = null;

    #[Locked]
    public ?string $pelacakanGagal = null;

    // dipanggil lewat wire:init, supaya halaman tidak menunggu jawaban kurir
    public function muatPelacakan(): void
    {
        $this->pelacakan = null;
        $this->pelacakanGagal = null;

        // setiap muat bisa memanggil kurir; "Coba lagi" yang diklik terus tidak boleh membanjiri provider
        $boleh = RateLimiter::attempt('lacak-paket:'.$this->pesanan->id, 10, function () {
            try {
                $this->pelacakan = $this->pesanan->lacakPaket(app(ShippingProvider::class))?->keArray();
            } catch (PelacakanGagal $e) {
                $this->pelacakanGagal = $e->getMessage();
            }
        });

        if ($boleh === false) {
            $this->pelacakanGagal = 'Terlalu sering memuat pelacakan. Coba lagi sebentar lagi.';
        }
    }
}

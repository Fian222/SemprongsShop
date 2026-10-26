<?php

namespace App\Livewire;

use App\Pengiriman\Area;
use App\Pengiriman\OngkirGagal;
use App\Pengiriman\ShippingProvider;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Component;

// Pencarian area tujuan dari ShippingProvider; dipakai dengan wire:model="form.area" di formulir alamat
class PilihArea extends Component
{
    // ['id' => ..., 'nama' => ..., 'kode_pos' => ...] atau null; diperiksa ulang ke provider saat alamat disimpan
    #[Modelable]
    public $area = null;

    public $cari = '';

    #[Locked]
    public array $hasil = [];

    public ?string $pesan = null;

    public function updatedCari()
    {
        $this->hasil = [];
        $this->pesan = null;

        if (mb_strlen(trim($this->cari)) < 3) {
            return;
        }

        try {
            $this->hasil = collect(app(ShippingProvider::class)->cariArea(trim($this->cari)))
                ->take(10)
                ->map(fn (Area $area) => $area->keArray())
                ->all();
        } catch (OngkirGagal $e) {
            report($e);
            $this->pesan = $e->getMessage();

            return;
        }

        if (! $this->hasil) {
            $this->pesan = 'Area tidak ditemukan. Coba nama kecamatan atau kota.';
        }
    }

    public function pilih($index)
    {
        $this->area = $this->hasil[$index] ?? $this->area;
        $this->reset('cari', 'hasil', 'pesan');
    }

    public function ganti()
    {
        $this->area = null;
    }

    public function render()
    {
        return view('livewire.pilih-area');
    }
}

<?php

namespace App\Livewire\Admin;

use App\Enums\StatusPesanan;
use App\Exceptions\TransisiTidakSah;
use App\Livewire\Concerns\MemuatPelacakan;
use App\Models\Pesanan;
use Livewire\Component;

class DetailPesanan extends Component
{
    use MemuatPelacakan;

    public Pesanan $pesanan;

    // nomor resi dari kurir, diinput saat pesanan dikirim
    public $resi = '';

    public function proses()
    {
        $this->ubah(fn () => $this->pesanan->ubahStatus(StatusPesanan::Diproses), 'Pesanan ditandai Diproses.');
    }

    public function kirim()
    {
        $this->resi = strtoupper(trim((string) $this->resi));
        $this->validate(
            ['resi' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9-]+$/']],
            ['resi.regex' => 'Nomor resi hanya berisi huruf, angka, dan tanda hubung.'],
            ['resi' => 'nomor resi'],
        );

        $this->ubah(fn () => $this->pesanan->kirim($this->resi), 'Pesanan ditandai Dikirim. Pelanggan bisa melacak paketnya.');

        // panel pelacakan baru muncul sesudah resi tersimpan, jadi wire:init-nya tidak ikut berjalan
        if ($this->pesanan->pengiriman?->resi) {
            $this->muatPelacakan();
        }
    }

    public function selesaikan()
    {
        $this->ubah(fn () => $this->pesanan->ubahStatus(StatusPesanan::Selesai), 'Pesanan ditandai Selesai.');
    }

    public function batalkan()
    {
        $this->ubah(fn () => $this->pesanan->ubahStatus(StatusPesanan::Dibatalkan), 'Pesanan dibatalkan dan stoknya dikembalikan.');
    }

    // transisi ditolak mesin status bila status pesanan sudah berubah di tab atau proses lain
    protected function ubah(callable $transisi, string $berhasil): void
    {
        try {
            $transisi();
        } catch (TransisiTidakSah $e) {
            $this->addError('status', $e->getMessage());
            $this->pesanan->refresh();

            return;
        }

        $this->pesanan->refresh();
        session()->flash('message', $berhasil);
    }

    public function render()
    {
        $this->pesanan->loadMissing('user', 'items', 'pembayaran', 'pengiriman');

        return view('livewire.admin.detail-pesanan')
            ->extends('layouts.adminapp')
            ->section('content');
    }
}

<?php

namespace App\Livewire;

use App\Models\Pesanan;
use App\Pembayaran\PaymentGateway;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

class DetailPesanan extends Component
{
    public Pesanan $pesanan;

    // transaksi yang langsung dibuka begitu halaman tampil (datang dari checkout dengan ?bayar=1)
    #[Locked]
    public ?array $bukaOtomatis = null;

    public function mount(Pesanan $pesanan)
    {
        // pesanan orang lain diperlakukan seperti tidak ada
        abort_unless($pesanan->milik(Auth::user()), 404);

        if (request()->boolean('bayar')) {
            $this->bukaOtomatis = $this->siapkanPembayaran();
        }
    }

    // membuka (ulang) jendela pembayaran
    public function bayar()
    {
        $this->pesanan->refresh();

        if ($transaksi = $this->siapkanPembayaran()) {
            $this->dispatch('buka-pembayaran', ...$transaksi);
        }
    }

    // token dan URL jendela pembayaran, atau null bila pesanan tidak bisa dibayar atau gateway gagal
    protected function siapkanPembayaran(): ?array
    {
        if (! $this->pesanan->menungguPembayaran()) {
            return null;
        }

        try {
            $pembayaran = $this->pesanan->siapkanPembayaran(app(PaymentGateway::class));
        } catch (Throwable $e) {
            report($e);
            $this->addError('bayar', 'Jendela pembayaran belum bisa dibuka. Coba lagi sebentar lagi.');

            return null;
        }

        return ['token' => $pembayaran->token, 'url' => $pembayaran->redirect_url];
    }

    public function render()
    {
        $this->pesanan->loadMissing('items', 'pembayaran');

        return view('livewire.detail-pesanan', [
            'skripJendela' => app(PaymentGateway::class)->skripJendela(),
        ])
            ->extends('layouts.app')
            ->section('content');
    }
}

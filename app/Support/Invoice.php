<?php

namespace App\Support;

use App\Models\Pesanan;
use Barryvdh\DomPDF\Facade\Pdf;

// Invoice PDF satu Pesanan, untuk diunduh dan dilampirkan di email pembayaran
class Invoice
{
    public function __construct(private Pesanan $pesanan) {}

    public function namaFile(): string
    {
        return "invoice-{$this->pesanan->nomor}.pdf";
    }

    public function html(): string
    {
        return view('invoice', ['pesanan' => $this->pesanan->loadMissing('user', 'items', 'pengiriman', 'pembayaran')])->render();
    }

    public function pdf(): string
    {
        // subsetting: hanya huruf yang terpakai dari DejaVu yang ditanam, jadi PDF tetap puluhan KB
        return Pdf::loadHTML($this->html())->setPaper('a4')->setOption('isFontSubsettingEnabled', true)->output();
    }
}

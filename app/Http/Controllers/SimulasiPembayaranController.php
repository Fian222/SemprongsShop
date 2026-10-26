<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Pembayaran\PaymentGateway;
use App\Pembayaran\ProsesNotifikasiPembayaran;
use App\Pembayaran\SimulasiGateway;
use Illuminate\Http\Request;

/**
 * Pengganti jendela Snap saat PEMBAYARAN_DRIVER=simulasi. Tombolnya membuat notifikasi berformat Midtrans
 * dan menjalankannya lewat jalur yang sama dengan webhook.
 */
class SimulasiPembayaranController extends Controller
{
    public function tampil(Request $request, Pesanan $pesanan, PaymentGateway $gateway)
    {
        $this->pastikanBoleh($request, $pesanan, $gateway);

        return view('pembayaran.simulasi', ['pesanan' => $pesanan]);
    }

    public function kirim(Request $request, Pesanan $pesanan, PaymentGateway $gateway, ProsesNotifikasiPembayaran $proses)
    {
        $this->pastikanBoleh($request, $pesanan, $gateway);
        $status = $request->validate(['status' => ['required', 'in:settlement,pending,expire,cancel']])['status'];

        $proses->proses($gateway->bacaNotifikasi($gateway->notifikasi($pesanan, $status)));

        return redirect()->route('pesanan.detail', $pesanan);
    }

    private function pastikanBoleh(Request $request, Pesanan $pesanan, PaymentGateway $gateway): void
    {
        abort_unless($gateway instanceof SimulasiGateway, 404);
        abort_unless($pesanan->milik($request->user()), 404);
    }
}

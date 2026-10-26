<?php

namespace App\Http\Controllers;

use App\Pembayaran\NotifikasiTidakSah;
use App\Pembayaran\PaymentGateway;
use App\Pembayaran\ProsesNotifikasiPembayaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Webhook notifikasi pembayaran (Midtrans: Settings > Payment > Notification URL)
class NotifikasiPembayaranController extends Controller
{
    public function __invoke(Request $request, PaymentGateway $gateway, ProsesNotifikasiPembayaran $proses): JsonResponse
    {
        try {
            $notifikasi = $gateway->bacaNotifikasi($request->all());
        } catch (NotifikasiTidakSah $e) {
            return response()->json(['pesan' => $e->getMessage()], 403);
        }

        if (! $proses->proses($notifikasi)) {
            return response()->json(['pesan' => 'Pesanan tidak dikenal.'], 404);
        }

        return response()->json(['pesan' => 'Diterima.']);
    }
}

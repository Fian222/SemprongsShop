<?php

namespace App\Pembayaran;

use App\Enums\StatusPembayaran;
use App\Enums\StatusPesanan;
use App\Exceptions\TransisiTidakSah;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use Illuminate\Support\Facades\Log;

/**
 * Menjalankan kejadian pembayaran pada pesanan. Idempoten: notifikasi yang sama (Midtrans bisa mengirim ulang)
 * tidak mengubah apa-apa lagi. Nominal yang tidak cocok dengan total pesanan tidak pernah membuat pesanan Dibayar.
 */
class ProsesNotifikasiPembayaran
{
    public function __construct(private PaymentGateway $gateway) {}

    // mengembalikan pesanan yang terkena, atau null bila referensinya tidak dikenal
    public function proses(Notifikasi $notifikasi): ?Pesanan
    {
        $pesanan = Pembayaran::firstWhere('referensi', $notifikasi->referensi)?->pesanan
            ?? Pesanan::firstWhere('nomor', $notifikasi->referensi);
        if (! $pesanan) {
            return null;
        }

        $status = StatusPembayaran::dari($notifikasi->kejadian);

        if ($status === StatusPembayaran::Dibayar && $notifikasi->nominal !== $pesanan->total) {
            Log::warning('Nominal pembayaran tidak cocok', ['pesanan' => $pesanan->nomor, 'nominal' => $notifikasi->nominal, 'total' => $pesanan->total]);
            $status = StatusPembayaran::NominalTidakCocok;
        } elseif ($tujuan = $this->statusPesanan($status)) {
            try {
                if ($pesanan->status !== $tujuan) {
                    $pesanan->ubahStatus($tujuan);
                }
            } catch (TransisiTidakSah $e) {
                // mis. uang masuk sesudah pesanan kedaluwarsa dan stoknya kembali; admin perlu menanganinya
                Log::warning($e->getMessage(), ['pesanan' => $pesanan->nomor, 'transaksi' => $notifikasi->transaksiId]);
                $status = $status === StatusPembayaran::Dibayar ? StatusPembayaran::DibayarTerlambat : null;
            }
        }

        $this->catat($pesanan, $notifikasi, $status);

        return $pesanan;
    }

    private function statusPesanan(StatusPembayaran $status): ?StatusPesanan
    {
        return match ($status) {
            StatusPembayaran::Dibayar => StatusPesanan::Dibayar,
            StatusPembayaran::Kedaluwarsa => StatusPesanan::Kedaluwarsa,
            StatusPembayaran::Dibatalkan => StatusPesanan::Dibatalkan,
            default => null,
        };
    }

    private function catat(Pesanan $pesanan, Notifikasi $notifikasi, ?StatusPembayaran $status): void
    {
        $pembayaran = $pesanan->pembayaran ?? $pesanan->pembayaran()->make([
            'provider' => $this->gateway->nama(),
            'referensi' => $notifikasi->referensi,
            'nominal' => $pesanan->total,
        ]);

        // pembayaran yang sudah masuk tidak diturunkan lagi oleh notifikasi yang datang terlambat atau berulang
        if ($pembayaran->status === StatusPembayaran::Dibayar && $status !== StatusPembayaran::Dibayar) {
            return;
        }

        $pembayaran->fill([
            'transaksi_id' => $notifikasi->transaksiId,
            'metode' => $notifikasi->metode,
            'payload' => $notifikasi->payload,
        ]);
        if ($status) {
            $pembayaran->status = $status;
        }
        if ($status === StatusPembayaran::Dibayar) {
            $pembayaran->dibayar_at ??= now();
        }
        $pembayaran->save();
    }
}

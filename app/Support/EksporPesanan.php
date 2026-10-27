<?php

namespace App\Support;

use App\Models\ItemPesanan;
use App\Models\Pesanan;
use Carbon\CarbonInterface;

// Pesanan yang dibuat dalam satu rentang tanggal, sebagai CSV untuk dibuka di spreadsheet
class EksporPesanan
{
    private const KOLOM = [
        'Nomor', 'Tanggal pesan', 'Status', 'Pelanggan', 'Email', 'Penerima', 'No. HP', 'Area tujuan', 'Barang',
        'Subtotal', 'Diskon', 'Voucher', 'Ongkir', 'Total', 'Kurir', 'Resi', 'Dibayar pada',
    ];

    /**
     * Aturan validasi rentang tanggal (dari, sampai) beserta pesannya.
     *
     * @return array{0: array, 1: array}
     */
    public static function aturan(): array
    {
        return [[
            'dari' => ['required', 'date_format:Y-m-d'],
            'sampai' => ['required', 'date_format:Y-m-d', 'after_or_equal:dari'],
        ], [
            'sampai.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal.',
        ]];
    }

    public function __construct(private CarbonInterface $dari, private CarbonInterface $sampai) {}

    public function namaFile(): string
    {
        return "pesanan-{$this->dari->toDateString()}-sampai-{$this->sampai->toDateString()}.csv";
    }

    /** @param resource $tujuan */
    public function tulis($tujuan): void
    {
        // BOM supaya Excel membaca huruf non-ASCII (mis. ×) sebagai UTF-8
        fwrite($tujuan, "\u{FEFF}");
        $this->baris($tujuan, self::KOLOM);

        Pesanan::with('user', 'items', 'pengiriman')
            ->whereBetween('created_at', [$this->dari->copy()->startOfDay(), $this->sampai->copy()->endOfDay()])
            ->lazyById(200)
            ->each(fn (Pesanan $pesanan) => $this->baris($tujuan, [
                $pesanan->nomor,
                $this->waktu($pesanan->created_at),
                $pesanan->status->label(),
                $pesanan->user->name,
                $pesanan->user->email,
                $pesanan->alamat['penerima'],
                $pesanan->alamat['no_hp'],
                $pesanan->alamat['area_nama'],
                $pesanan->items->map(fn (ItemPesanan $item) => "{$item->nama_produk} – {$item->nama_varian} × {$item->jumlah}")->implode('; '),
                $pesanan->subtotal,
                $pesanan->diskon,
                $pesanan->voucher_kode,
                $pesanan->ongkir,
                $pesanan->total,
                $pesanan->pengiriman?->label(),
                $pesanan->pengiriman?->resi,
                $this->waktu($pesanan->dibayar_at),
            ]));
    }

    /** @param resource $tujuan */
    private function baris($tujuan, array $nilai): void
    {
        fputcsv($tujuan, array_map($this->aman(...), $nilai), eol: "\r\n", escape: '');
    }

    // teks yang diawali = + - @ dijalankan spreadsheet sebagai rumus; awalan ' membuatnya tetap teks
    private function aman(string|int|null $nilai): string|int
    {
        $nilai ??= '';

        return is_string($nilai) && preg_match('/^[=+\-@\t\r]/', $nilai) ? "'".$nilai : $nilai;
    }

    private function waktu(?CarbonInterface $waktu): ?string
    {
        return $waktu?->format('Y-m-d H:i:s');
    }
}

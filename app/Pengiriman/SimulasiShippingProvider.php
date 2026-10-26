<?php

namespace App\Pengiriman;

use Illuminate\Support\Str;

/**
 * Ongkir tanpa jaringan untuk pengembangan dan feature test: beberapa area contoh dan tarif yang dihitung dari berat.
 */
class SimulasiShippingProvider implements ShippingProvider
{
    private const AREA = [
        'SIMULASI-40115' => 'Bandung Wetan, Bandung, Jawa Barat. 40115',
        'SIMULASI-40132' => 'Coblong, Bandung, Jawa Barat. 40132',
        'SIMULASI-12250' => 'Pesanggrahan, Jakarta Selatan, DKI Jakarta. 12250',
        'SIMULASI-10110' => 'Gambir, Jakarta Pusat, DKI Jakarta. 10110',
        'SIMULASI-60271' => 'Genteng, Surabaya, Jawa Timur. 60271',
        'SIMULASI-55213' => 'Gondokusuman, Yogyakarta, DI Yogyakarta. 55213',
        'SIMULASI-80361' => 'Kuta, Badung, Bali. 80361',
    ];

    // [kode kurir, nama kurir, kode layanan, nama layanan, tarif dasar, tarif per kg, estimasi]
    private const LAYANAN = [
        ['jne', 'JNE', 'reg', 'Reguler', 10000, 8000, '2–3 hari'],
        ['sicepat', 'SiCepat', 'reg', 'Reguler', 9000, 7500, '1–2 hari'],
        ['anteraja', 'AnterAja', 'reg', 'Reguler', 9500, 7000, '2–4 hari'],
    ];

    public function cariArea(string $teks): array
    {
        return collect(self::AREA)
            ->filter(fn (string $nama) => Str::contains($nama, trim($teks), ignoreCase: true))
            ->map(fn (string $nama, string $id) => new Area($id, $nama, Str::afterLast($nama, '. ')))
            ->values()
            ->all();
    }

    public function tarif(string $areaTujuan, array $barang): array
    {
        if (! isset(self::AREA[$areaTujuan])) {
            throw OngkirGagal::provider("Area simulasi {$areaTujuan} tidak dikenal.");
        }

        $berat = BarangKiriman::totalBerat($barang);
        $kg = max(1, (int) ceil($berat / 1000));

        return array_map(fn (array $l) => new Tarif($l[0], $l[1], $l[2], $l[3], $l[4] + $l[5] * $kg, $l[6], $berat), self::LAYANAN);
    }

    // resi berawalan SAMPAI sudah diterima, berawalan GAGAL belum dikenal kurir, selain itu masih di jalan
    public function lacak(string $kurir, string $resi): Pelacakan
    {
        if (str_starts_with($resi, 'GAGAL')) {
            throw PelacakanGagal::provider("Resi simulasi {$resi} tidak dikenal.");
        }

        $riwayat = [
            new KejadianLacak(now()->toImmutable()->subDay(), 'Paket diterima di gudang '.strtoupper($kurir).' Bandung'),
            new KejadianLacak(now()->toImmutable()->subHours(6), 'Paket dalam perjalanan ke kota tujuan'),
        ];

        if (! str_starts_with($resi, 'SAMPAI')) {
            return new Pelacakan('Paket dalam perjalanan', false, array_reverse($riwayat));
        }

        $riwayat[] = new KejadianLacak(now()->toImmutable()->subHour(), 'Paket diterima oleh yang bersangkutan');

        return new Pelacakan('Paket diterima', true, array_reverse($riwayat));
    }
}

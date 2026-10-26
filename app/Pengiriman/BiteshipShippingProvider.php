<?php

namespace App\Pengiriman;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

// Adapter Biteship: https://biteship.com/id/docs/api
class BiteshipShippingProvider implements ShippingProvider
{
    // status pelacakan Biteship: https://biteship.com/id/docs/api/trackings/overview
    private const STATUS_LACAK = [
        'confirmed' => 'Menunggu dijemput kurir',
        'scheduled' => 'Menunggu dijemput kurir',
        'allocated' => 'Menunggu dijemput kurir',
        'picking_up' => 'Kurir menuju lokasi penjemputan',
        'picked' => 'Paket sudah dijemput kurir',
        'dropping_off' => 'Paket sedang diantar',
        'on_hold' => 'Paket tertahan di kurir',
        'delivered' => 'Paket diterima',
        'return_in_transit' => 'Paket dalam perjalanan kembali',
        'returned' => 'Paket dikembalikan ke toko',
        'rejected' => 'Paket ditolak penerima',
        'cancelled' => 'Pengiriman dibatalkan kurir',
        'courier_not_found' => 'Kurir belum ditemukan',
        'disposed' => 'Paket dimusnahkan kurir',
    ];

    public function __construct(private string $apiKey, private string $areaAsal, private string $kurir) {}

    public function cariArea(string $teks): array
    {
        $respons = $this->kirim(fn (PendingRequest $http) => $http->get('/v1/maps/areas', [
            'countries' => 'ID',
            'input' => $teks,
            'type' => 'single',
        ]));

        return $this->baca(fn () => collect($respons->json('areas', []))
            ->map(fn (array $area) => new Area($area['id'], $area['name'], isset($area['postal_code']) ? (string) $area['postal_code'] : null))
            ->all());
    }

    public function tarif(string $areaTujuan, array $barang): array
    {
        if ($this->areaAsal === '') {
            throw OngkirGagal::provider('BITESHIP_AREA_ASAL belum diisi.');
        }

        $respons = $this->kirim(fn (PendingRequest $http) => $http->post('/v1/rates/couriers', [
            'origin_area_id' => $this->areaAsal,
            'destination_area_id' => $areaTujuan,
            'couriers' => $this->kurir,
            'items' => array_map(fn (BarangKiriman $b) => [
                'name' => $b->nama,
                'value' => $b->nilai,
                'weight' => $b->berat,
                'quantity' => $b->jumlah,
                'category' => 'food_and_drink',
            ], $barang),
        ]));

        $berat = BarangKiriman::totalBerat($barang);

        return $this->baca(fn () => collect($respons->json('pricing', []))
            ->map(fn (array $harga) => new Tarif(
                kurir: $harga['courier_code'],
                namaKurir: $harga['courier_name'],
                layanan: $harga['courier_service_code'],
                namaLayanan: $harga['courier_service_name'],
                ongkir: (int) $harga['price'],
                estimasi: $this->estimasi($harga['shipment_duration_range'] ?? '', $harga['shipment_duration_unit'] ?? 'days'),
                berat: $berat,
            ))
            ->all());
    }

    public function lacak(string $kurir, string $resi): Pelacakan
    {
        $respons = $this->kirim(fn (PendingRequest $http) => $http->get('/v1/trackings/'.rawurlencode($resi).'/couriers/'.rawurlencode($kurir)),
            PelacakanGagal::class);

        return $this->baca(fn () => new Pelacakan(
            status: self::STATUS_LACAK[$respons->json('status')] ?? 'Dalam pengiriman',
            sampai: $respons->json('status') === 'delivered',
            riwayat: collect($respons->json('history', []))
                ->map(fn (array $k) => new KejadianLacak(CarbonImmutable::parse($k['updated_at']), $k['note']))
                ->sortByDesc(fn (KejadianLacak $k) => $k->waktu->getTimestamp())
                ->values()
                ->all(),
        ), PelacakanGagal::class);
    }

    /**
     * @param  class-string<OngkirGagal|PelacakanGagal>  $gagal
     */
    private function kirim(callable $permintaan, string $gagal = OngkirGagal::class): Response
    {
        if ($this->apiKey === '') {
            throw $gagal::provider('BITESHIP_API_KEY belum diisi.');
        }

        try {
            $respons = $permintaan(Http::baseUrl('https://api.biteship.com')
                ->withHeaders(['authorization' => $this->apiKey])
                ->acceptJson()
                ->asJson()
                ->timeout(10));
        } catch (ConnectionException $e) {
            throw $gagal::provider($e->getMessage());
        }

        if ($respons->failed() || $respons->json('success') !== true) {
            throw $gagal::provider($respons->body());
        }

        return $respons;
    }

    /**
     * Respons yang bentuknya tidak dikenal (kolom hilang, tipe lain) diperlakukan seperti gangguan provider.
     *
     * @param  class-string<OngkirGagal|PelacakanGagal>  $gagal
     */
    private function baca(callable $baca, string $gagal = OngkirGagal::class): mixed
    {
        try {
            return $baca();
        } catch (\Throwable $e) {
            throw $gagal::provider('Respons Biteship tidak dikenal: '.$e->getMessage());
        }
    }

    // "2 - 3" + "days" menjadi "2–3 hari"
    private function estimasi(string $rentang, string $satuan): string
    {
        $satuan = ['days' => 'hari', 'hours' => 'jam'][$satuan] ?? $satuan;

        return trim(str_replace(' - ', '–', $rentang).' '.$satuan);
    }
}

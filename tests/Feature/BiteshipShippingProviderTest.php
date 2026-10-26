<?php

namespace Tests\Feature;

use App\Pengiriman\BarangKiriman;
use App\Pengiriman\BiteshipShippingProvider;
use App\Pengiriman\OngkirGagal;
use App\Pengiriman\PelacakanGagal;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

// Test kontrak adapter Biteship (https://biteship.com/id/docs/api): bentuk request dan pembacaan respons, tanpa jaringan
class BiteshipShippingProviderTest extends TestCase
{
    protected function provider(): BiteshipShippingProvider
    {
        return new BiteshipShippingProvider('biteship_test.KUNCIUJI', 'IDNP9IDNC22IDND256IDZ40115', 'jne,sicepat');
    }

    public function test_mencari_area_tujuan()
    {
        Http::fake(['api.biteship.com/v1/maps/areas*' => Http::response([
            'success' => true,
            'areas' => [[
                'id' => 'IDNP6IDNC148IDND843IDZ12250',
                'name' => 'Pesanggrahan, Jakarta Selatan, DKI Jakarta. 12250',
                'country_name' => 'Indonesia',
                'country_code' => 'ID',
                'administrative_division_level_1_name' => 'DKI Jakarta',
                'administrative_division_level_2_name' => 'Jakarta Selatan',
                'administrative_division_level_3_name' => 'Pesanggrahan',
                'postal_code' => 12250,
            ]],
        ])]);

        $areas = $this->provider()->cariArea('Jakarta Selatan');

        $this->assertSame('IDNP6IDNC148IDND843IDZ12250', $areas[0]->id);
        $this->assertSame('Pesanggrahan, Jakarta Selatan, DKI Jakarta. 12250', $areas[0]->nama);
        $this->assertSame('12250', $areas[0]->kodePos);
        Http::assertSent(fn (Request $request) => $request->method() === 'GET'
            && str_starts_with($request->url(), 'https://api.biteship.com/v1/maps/areas?')
            && $request['countries'] === 'ID'
            && $request['input'] === 'Jakarta Selatan'
            && $request['type'] === 'single'
            && $request->header('authorization') === ['biteship_test.KUNCIUJI']);
    }

    public function test_menghitung_tarif_dari_area_asal_ke_area_tujuan()
    {
        Http::fake(['api.biteship.com/v1/rates/couriers' => Http::response([
            'success' => true,
            'object' => 'courier_pricing',
            'pricing' => [
                [
                    'courier_name' => 'JNE', 'courier_code' => 'jne',
                    'courier_service_name' => 'Reguler', 'courier_service_code' => 'reg',
                    'duration' => '2 - 3 days', 'shipment_duration_range' => '2 - 3', 'shipment_duration_unit' => 'days',
                    'price' => 18000, 'type' => 'reg',
                ],
                [
                    'courier_name' => 'SiCepat', 'courier_code' => 'sicepat',
                    'courier_service_name' => 'Besok Sampai Tujuan', 'courier_service_code' => 'best',
                    'duration' => '1 days', 'shipment_duration_range' => '1', 'shipment_duration_unit' => 'days',
                    'price' => 32000, 'type' => 'best',
                ],
            ],
        ])]);

        $tarif = $this->provider()->tarif('IDNP6IDNC148IDND843IDZ12250', [
            new BarangKiriman('Semprong Pandan Toples kecil', 45000, 300, 2),
            new BarangKiriman('Hampers Pita Marun Kotak kayu', 245000, 2100, 1),
        ]);

        $this->assertSame(['jne:reg', 'JNE', 'Reguler', 18000, '2–3 hari', 2700],
            [$tarif[0]->kunci(), $tarif[0]->namaKurir, $tarif[0]->namaLayanan, $tarif[0]->ongkir, $tarif[0]->estimasi, $tarif[0]->berat]);
        $this->assertSame(['sicepat:best', '1 hari'], [$tarif[1]->kunci(), $tarif[1]->estimasi]);

        Http::assertSent(fn (Request $request) => $request->method() === 'POST'
            && $request->url() === 'https://api.biteship.com/v1/rates/couriers'
            && $request->header('authorization') === ['biteship_test.KUNCIUJI']
            && $request['origin_area_id'] === 'IDNP9IDNC22IDND256IDZ40115'
            && $request['destination_area_id'] === 'IDNP6IDNC148IDND843IDZ12250'
            && $request['couriers'] === 'jne,sicepat'
            && $request['items'] === [
                ['name' => 'Semprong Pandan Toples kecil', 'value' => 45000, 'weight' => 300, 'quantity' => 2, 'category' => 'food_and_drink'],
                ['name' => 'Hampers Pita Marun Kotak kayu', 'value' => 245000, 'weight' => 2100, 'quantity' => 1, 'category' => 'food_and_drink'],
            ]);
    }

    public function test_penolakan_atau_gangguan_biteship_menjadi_ongkir_gagal()
    {
        Http::fake(['api.biteship.com/*' => Http::sequence()
            ->push(['success' => false, 'error' => 'Invalid area id', 'code' => 40001001], 400)
            ->push('Bad Gateway', 502)]);

        foreach (['tarif', 'cariArea'] as $aksi) {
            try {
                $aksi === 'tarif'
                    ? $this->provider()->tarif('area-salah', [new BarangKiriman('Semprong', 45000, 300, 1)])
                    : $this->provider()->cariArea('Bandung');
                $this->fail("$aksi seharusnya gagal");
            } catch (OngkirGagal $e) {
                $this->assertSame('Ongkir belum bisa dihitung saat ini. Coba lagi sebentar lagi.', $e->getMessage());
            }
        }
    }

    public function test_tanpa_api_key_atau_area_asal_tidak_menghubungi_biteship()
    {
        Http::fake();

        $this->expectException(OngkirGagal::class);

        try {
            (new BiteshipShippingProvider('', '', 'jne'))->tarif('IDNP6IDNC148IDND843IDZ12250', [new BarangKiriman('Semprong', 45000, 300, 1)]);
        } finally {
            Http::assertNothingSent();
        }
    }

    public function test_melacak_paket_dari_kurir_dan_resi()
    {
        Http::fake(['api.biteship.com/v1/trackings/*' => Http::response([
            'success' => true,
            'messsage' => 'Successfully get tracking info',
            'object' => 'tracking',
            'id' => '6051861741a37414e6637fab',
            'waybill_id' => 'JNE0012345678',
            'courier' => ['company' => 'jne', 'driver_name' => null, 'driver_phone' => null],
            'origin' => ['contact_name' => null, 'address' => null],
            'destination' => ['contact_name' => null, 'address' => null],
            'history' => [
                ['note' => 'SHIPMENT RECEIVED BY JNE COUNTER OFFICER AT [BANDUNG]', 'updated_at' => '2026-10-26T09:00:00+07:00', 'status' => 'picked'],
                ['note' => 'WITH DELIVERY COURIER [JAKARTA SELATAN]', 'updated_at' => '2026-10-27T08:15:00+07:00', 'status' => 'dropping_off'],
                ['note' => 'DELIVERED TO [BUDI | 27-10-2026 13:40 | JAKARTA]', 'updated_at' => '2026-10-27T13:40:00+07:00', 'status' => 'delivered'],
            ],
            'link' => '-',
            'order_id' => null,
            'status' => 'delivered',
        ])]);

        $pelacakan = $this->provider()->lacak('jne', 'JNE0012345678');

        $this->assertTrue($pelacakan->sampai);
        $this->assertSame('Paket diterima', $pelacakan->status);
        // terbaru di atas
        $this->assertSame('DELIVERED TO [BUDI | 27-10-2026 13:40 | JAKARTA]', $pelacakan->riwayat[0]->keterangan);
        $this->assertSame('2026-10-27 13:40', $pelacakan->riwayat[0]->waktu->timezone('Asia/Jakarta')->format('Y-m-d H:i'));
        $this->assertCount(3, $pelacakan->riwayat);
        Http::assertSent(fn (Request $request) => $request->method() === 'GET'
            && $request->url() === 'https://api.biteship.com/v1/trackings/JNE0012345678/couriers/jne'
            && $request->header('authorization') === ['biteship_test.KUNCIUJI']);
    }

    public function test_paket_yang_masih_di_jalan_belum_sampai()
    {
        Http::fake(['api.biteship.com/*' => Http::response([
            'success' => true,
            'history' => [['note' => 'Paket dijemput kurir', 'updated_at' => '2026-10-26T09:00:00+07:00', 'status' => 'picked']],
            'status' => 'dropping_off',
        ])]);

        $pelacakan = $this->provider()->lacak('sicepat', '0040012345');

        $this->assertFalse($pelacakan->sampai);
        $this->assertSame('Paket sedang diantar', $pelacakan->status);
    }

    public function test_resi_yang_belum_dikenal_kurir_menjadi_pelacakan_gagal()
    {
        Http::fake(['api.biteship.com/*' => Http::response(['success' => false, 'error' => 'Waybill not found', 'code' => 40003002], 400)]);

        $this->expectException(PelacakanGagal::class);
        $this->expectExceptionMessage('Riwayat pelacakan belum bisa dimuat.');

        $this->provider()->lacak('jne', 'SALAH');
    }

    public function test_respons_yang_tidak_lengkap_menjadi_ongkir_gagal()
    {
        Http::fake(['api.biteship.com/*' => Http::response(['success' => true, 'pricing' => [['courier_name' => 'JNE', 'price' => 18000]]])]);

        $this->expectException(OngkirGagal::class);

        $this->provider()->tarif('IDNP6IDNC148IDND843IDZ12250', [new BarangKiriman('Semprong', 45000, 300, 1)]);
    }
}

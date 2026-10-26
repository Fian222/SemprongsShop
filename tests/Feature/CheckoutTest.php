<?php

namespace Tests\Feature;

use App\Enums\StatusPesanan;
use App\Exceptions\KeranjangDitolak;
use App\Livewire\Checkout;
use App\Livewire\DetailPesanan;
use App\Models\Keranjang;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\User;
use App\Models\Varian;
use App\Pengiriman\ShippingProvider;
use App\Pengiriman\SimulasiShippingProvider;
use App\Pengiriman\Tarif;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    protected function buatVarian(array $data = []): Varian
    {
        return Varian::factory()
            ->for(Produk::factory()->state(['nama' => 'Semprong Pandan', 'slug' => 'semprong-pandan']))
            ->create(array_merge(['nama' => 'Toples kecil', 'sku' => 'SMP-PDN-250', 'harga' => 45000, 'stok' => 5, 'berat' => 300], $data));
    }

    protected function pelangganDenganAlamat(): User
    {
        $user = User::factory()->create(['name' => 'Budi']);
        $this->alamatUntuk($user);

        return $user;
    }

    public function test_checkout_membuat_pesanan_memesan_stok_dan_mengosongkan_keranjang()
    {
        $this->travelTo(now()->setDate(2026, 10, 26)->setTime(10, 0));
        $this->actingAs($user = $this->pelangganDenganAlamat());
        $varian = $this->buatVarian();
        Keranjang::milikPengunjungAtauBaru()->tambah($varian, 2);

        Livewire::test(Checkout::class)
            ->assertSet('alamatId', $user->alamats->first()->id)
            ->assertSee('Budi Santoso')
            ->assertSee('Rp 90.000')
            ->set('kurir', 'jne:reg')
            ->call('bayar')
            ->assertRedirect(route('pesanan.detail', 'SMP-20261026-0001').'?bayar=1');

        $pesanan = Pesanan::with('items', 'pembayaran')->firstOrFail();
        $this->assertSame(StatusPesanan::MenungguPembayaran, $pesanan->status);
        $this->assertSame([90000, 18000, 0, 108000], [$pesanan->subtotal, $pesanan->ongkir, $pesanan->diskon, $pesanan->total]);
        $this->assertSame('2026-10-27 10:00', $pesanan->batas_bayar->format('Y-m-d H:i'));
        $this->assertSame('Budi Santoso', $pesanan->alamat['penerima']);
        $this->assertSame(
            ['Semprong Pandan', 'Toples kecil', 'SMP-PDN-250', 45000, 2, 300],
            array_values($pesanan->items->first()->only('nama_produk', 'nama_varian', 'sku', 'harga', 'jumlah', 'berat')),
        );
        $this->assertSame(3, $varian->fresh()->stok);
        $this->assertSame(0, Keranjang::milikPengunjung()->items()->count());
    }

    public function test_harga_pesanan_dihitung_ulang_dari_varian_saat_checkout()
    {
        $this->actingAs($this->pelangganDenganAlamat());
        $varian = $this->buatVarian(['harga' => 45000]);
        Keranjang::milikPengunjungAtauBaru()->tambah($varian, 1);

        $halaman = Livewire::test(Checkout::class)->set('kurir', 'jne:reg');
        $varian->update(['harga' => 50000]);
        $halaman->call('bayar');

        $this->assertSame(50000, Pesanan::firstOrFail()->subtotal);
    }

    public function test_checkout_tanpa_alamat_ditolak()
    {
        $this->actingAs(User::factory()->create());
        Keranjang::milikPengunjungAtauBaru()->tambah($this->buatVarian(), 1);

        Livewire::test(Checkout::class)
            ->call('bayar')
            ->assertHasErrors('alamatId');

        $this->assertSame(0, Pesanan::count());
    }

    public function test_dua_checkout_yang_berebut_stok_terakhir_tidak_sama_sama_berhasil()
    {
        $varian = $this->buatVarian(['stok' => 1]);
        $pertama = $this->pelangganDenganAlamat();
        $kedua = $this->pelangganDenganAlamat();
        foreach ([$pertama, $kedua] as $user) {
            Keranjang::create(['user_id' => $user->id])->tambah($varian, 1);
        }

        $this->actingAs($pertama);
        Livewire::test(Checkout::class)->set('kurir', 'jne:reg')->call('bayar')->assertHasNoErrors();

        Auth::logout();
        $this->actingAs($kedua);
        Livewire::test(Checkout::class)
            ->set('kurir', 'jne:reg')
            ->call('bayar')
            ->assertHasErrors('keranjang')
            ->assertSee('Stok Toples kecil sedang habis.');

        $this->assertSame(1, Pesanan::count());
        $this->assertSame(0, $varian->fresh()->stok);
    }

    public function test_tamu_diarahkan_ke_login_dan_keranjang_kosong_kembali_ke_tas_belanja()
    {
        $this->get('/checkout')->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create());
        $this->get('/checkout')->assertRedirect(route('keranjang'));
    }

    public function test_checkout_ganda_dari_keranjang_yang_sama_tidak_membuat_pesanan_kosong()
    {
        $this->actingAs($user = $this->pelangganDenganAlamat());
        $keranjang = Keranjang::milikPengunjungAtauBaru();
        $keranjang->tambah($this->buatVarian(), 1);
        $tarif = app(ShippingProvider::class)->tarif('SIMULASI-40115', $keranjang->load('items.varian.produk')->barangKiriman())[0];
        Pesanan::buatDariKeranjang($keranjang, $user->alamats->first(), $tarif);

        $this->expectException(KeranjangDitolak::class);

        try {
            Pesanan::buatDariKeranjang($keranjang->refresh(), $user->alamats->first(), $tarif);
        } finally {
            $this->assertSame(1, Pesanan::count());
        }
    }

    public function test_halaman_pesanan_sesudah_checkout_langsung_membuka_jendela_pembayaran()
    {
        $this->actingAs($this->pelangganDenganAlamat());
        Keranjang::milikPengunjungAtauBaru()->tambah($this->buatVarian(), 1);
        Livewire::test(Checkout::class)->set('kurir', 'jne:reg')->call('bayar');
        $pesanan = Pesanan::firstOrFail();

        $this->get(route('pesanan.detail', $pesanan).'?bayar=1')->assertOk();

        $this->assertStringStartsWith('simulasi-', $pesanan->fresh()->pembayaran->token);
        Livewire::withQueryParams(['bayar' => 1])->test(DetailPesanan::class, ['pesanan' => $pesanan])
            ->assertSet('bukaOtomatis.token', $pesanan->fresh()->pembayaran->token);
    }

    public function test_checkout_menampilkan_pilihan_kurir_dan_total_termasuk_ongkir()
    {
        $this->actingAs($this->pelangganDenganAlamat());
        Keranjang::milikPengunjungAtauBaru()->tambah($this->buatVarian(), 2);

        // simulasi, 600 g dibulatkan ke 1 kg: JNE 18.000, SiCepat 16.500, AnterAja 16.500
        Livewire::test(Checkout::class)
            ->assertSee('JNE')
            ->assertSee('Rp 18.000')
            ->assertSee('1–2 hari')
            ->set('kurir', 'sicepat:reg')
            ->assertSee('Rp 106.500')
            ->call('bayar');

        $pesanan = Pesanan::with('pengiriman')->firstOrFail();
        $this->assertSame([90000, 16500, 106500], [$pesanan->subtotal, $pesanan->ongkir, $pesanan->total]);
        $this->assertSame(['sicepat', 'reg', 'SiCepat', 'Reguler', '1–2 hari', 600],
            array_values($pesanan->pengiriman->only('kurir', 'layanan', 'nama_kurir', 'nama_layanan', 'estimasi', 'berat')));
    }

    public function test_pilihan_kurir_di_browser_terkunci()
    {
        $this->actingAs($this->pelangganDenganAlamat());
        Keranjang::milikPengunjungAtauBaru()->tambah($this->buatVarian(), 2);

        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(Checkout::class)->set('pilihanKurir.0.ongkir', 1);
    }

    public function test_ongkir_dihitung_ulang_saat_bayar_dan_perubahan_harga_diberitahukan()
    {
        $this->actingAs($this->pelangganDenganAlamat());
        Keranjang::milikPengunjungAtauBaru()->tambah($this->buatVarian(), 2);
        $halaman = Livewire::test(Checkout::class)->set('kurir', 'jne:reg')->assertSee('Rp 18.000');

        // tarif JNE di provider naik sesudah halaman checkout terbuka
        $this->app->instance(ShippingProvider::class, new class extends SimulasiShippingProvider
        {
            public function tarif(string $areaTujuan, array $barang): array
            {
                return array_map(fn (Tarif $t) => new Tarif($t->kurir, $t->namaKurir, $t->layanan, $t->namaLayanan, $t->ongkir + 2000, $t->estimasi, $t->berat), parent::tarif($areaTujuan, $barang));
            }
        });

        $halaman->call('bayar')
            ->assertHasErrors('kurir')
            ->assertSee('Ongkir JNE Reguler berubah menjadi Rp 20.000');
        $this->assertSame(0, Pesanan::count());

        $halaman->call('bayar');
        $this->assertSame(20000, Pesanan::firstOrFail()->ongkir);
    }

    public function test_checkout_tidak_bisa_lanjut_tanpa_kurir()
    {
        $this->actingAs($this->pelangganDenganAlamat());
        Keranjang::milikPengunjungAtauBaru()->tambah($this->buatVarian(), 1);

        Livewire::test(Checkout::class)
            ->call('bayar')
            ->assertHasErrors('kurir');
        Livewire::test(Checkout::class)
            ->set('kurir', 'kurir:palsu')
            ->call('bayar')
            ->assertHasErrors('kurir');

        $this->assertSame(0, Pesanan::count());
    }

    public function test_alamat_tanpa_area_diminta_melengkapi_area()
    {
        $user = User::factory()->create();
        $this->alamatUntuk($user, ['area_id' => null, 'area_nama' => null]);
        $this->actingAs($user);
        Keranjang::milikPengunjungAtauBaru()->tambah($this->buatVarian(), 1);

        Livewire::test(Checkout::class)
            ->assertSee('Alamat ini belum punya area tujuan')
            ->set('kurir', 'jne:reg')
            ->call('bayar')
            ->assertHasErrors('kurir');

        $this->assertSame(0, Pesanan::count());
    }

    public function test_gangguan_provider_ongkir_menampilkan_pesan_dan_menahan_checkout()
    {
        config(['pengiriman.driver' => 'biteship', 'pengiriman.biteship.api_key' => 'biteship_test.UJI', 'pengiriman.biteship.area_asal' => 'IDNP9IDNC22IDND256IDZ40115']);
        Http::fake(['api.biteship.com/*' => Http::response('Service Unavailable', 503)]);
        $this->actingAs($this->pelangganDenganAlamat());
        Keranjang::milikPengunjungAtauBaru()->tambah($this->buatVarian(), 1);

        Livewire::test(Checkout::class)
            ->assertSee('Ongkir belum bisa dihitung saat ini.')
            ->set('kurir', 'jne:reg')
            ->call('bayar')
            ->assertHasErrors('kurir');

        $this->assertSame(0, Pesanan::count());
    }

    public function test_tarif_untuk_berat_yang_berbeda_dari_isi_keranjang_ditolak()
    {
        $user = $this->pelangganDenganAlamat();
        $keranjang = Keranjang::create(['user_id' => $user->id]);
        $keranjang->tambah($this->buatVarian(), 1);
        $tarif = app(ShippingProvider::class)->tarif('SIMULASI-40115', $keranjang->load('items.varian.produk')->barangKiriman())[0];
        $keranjang->items()->update(['jumlah' => 3]);

        $this->expectException(KeranjangDitolak::class);

        Pesanan::buatDariKeranjang($keranjang, $user->alamats->first(), $tarif);
    }

    public function test_coba_lagi_memuat_ulang_ongkir_sesudah_gangguan()
    {
        config(['pengiriman.driver' => 'biteship', 'pengiriman.biteship.api_key' => 'biteship_test.UJI', 'pengiriman.biteship.area_asal' => 'IDNP9IDNC22IDND256IDZ40115']);
        Http::fake(['api.biteship.com/*' => Http::sequence()
            ->push('Service Unavailable', 503)
            ->push(['success' => true, 'pricing' => [[
                'courier_name' => 'JNE', 'courier_code' => 'jne', 'courier_service_name' => 'Reguler', 'courier_service_code' => 'reg',
                'shipment_duration_range' => '2 - 3', 'shipment_duration_unit' => 'days', 'price' => 18000,
            ]]])]);
        $user = User::factory()->create();
        $this->alamatUntuk($user, ['area_id' => 'IDNP6IDNC148IDND843IDZ12250']);
        $this->actingAs($user);
        Keranjang::milikPengunjungAtauBaru()->tambah($this->buatVarian(), 1);

        Livewire::test(Checkout::class)
            ->assertSee('Ongkir belum bisa dihitung saat ini.')
            ->call('muatOngkir')
            ->assertDontSee('Ongkir belum bisa dihitung saat ini.')
            ->assertSee('JNE Reguler');
    }

    public function test_area_tanpa_kurir_mendapat_pesan_yang_jelas()
    {
        config(['pengiriman.driver' => 'biteship', 'pengiriman.biteship.api_key' => 'biteship_test.UJI', 'pengiriman.biteship.area_asal' => 'IDNP9IDNC22IDND256IDZ40115']);
        Http::fake(['api.biteship.com/*' => Http::response(['success' => true, 'pricing' => []])]);
        $user = User::factory()->create();
        $this->alamatUntuk($user, ['area_id' => 'IDNP6IDNC148IDND843IDZ12250']);
        $this->actingAs($user);
        Keranjang::milikPengunjungAtauBaru()->tambah($this->buatVarian(), 1);

        Livewire::test(Checkout::class)->assertSee('Belum ada kurir yang melayani area ini.');
    }
}

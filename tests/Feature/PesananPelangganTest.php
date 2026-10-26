<?php

namespace Tests\Feature;

use App\Enums\StatusPesanan;
use App\Livewire\DetailPesanan;
use App\Models\Keranjang;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\User;
use App\Models\Varian;
use App\Pembayaran\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use RuntimeException;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class PesananPelangganTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    protected User $user;

    protected Varian $varian;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->user = User::factory()->create();
        $this->varian = Varian::factory()
            ->for(Produk::factory()->state(['nama' => 'Semprong Pandan']))
            ->create(['nama' => 'Toples kecil', 'harga' => 45000, 'stok' => 5]);
    }

    protected function buatPesanan(): Pesanan
    {
        $keranjang = Keranjang::firstOrCreate(['user_id' => $this->user->id]);
        $keranjang->tambah($this->varian, 2);

        return $this->pesananDari($keranjang, $this->user->alamats()->first() ?? $this->alamatUntuk($this->user));
    }

    public function test_pelanggan_melihat_daftar_dan_detail_pesanannya()
    {
        $pesanan = $this->buatPesanan();
        $this->actingAs($this->user);

        $this->get('/pesanan')->assertOk()
            ->assertSee($pesanan->nomor)
            ->assertSee('Menunggu pembayaran')
            ->assertSee('Rp 108.000'); // 90.000 + JNE 18.000

        $this->get(route('pesanan.detail', $pesanan))->assertOk()
            ->assertSee('Semprong Pandan')
            ->assertSee('Toples kecil')
            ->assertSee('Budi Santoso')
            ->assertSee('Bayar sekarang');
    }

    public function test_snapshot_pesanan_tidak_berubah_saat_katalog_berubah()
    {
        $pesanan = $this->buatPesanan();
        $this->varian->update(['nama' => 'Kemasan baru', 'harga' => 99000]);
        $this->varian->produk->update(['nama' => 'Nama baru']);
        $this->actingAs($this->user);

        $this->get(route('pesanan.detail', $pesanan))
            ->assertSee('Semprong Pandan')
            ->assertSee('Rp 45.000')
            ->assertDontSee('Kemasan baru');
    }

    public function test_pesanan_belum_dibayar_bisa_dibayar_ulang_dengan_token_yang_sama()
    {
        $pesanan = $this->buatPesanan();
        $this->actingAs($this->user);

        $halaman = Livewire::test(DetailPesanan::class, ['pesanan' => $pesanan])->call('bayar');
        $token = $pesanan->fresh()->pembayaran->token;
        $halaman->assertDispatched('buka-pembayaran', token: $token, url: route('pembayaran.simulasi', $pesanan));

        Livewire::test(DetailPesanan::class, ['pesanan' => $pesanan])
            ->call('bayar')
            ->assertDispatched('buka-pembayaran', token: $token);
    }

    public function test_pesanan_yang_lewat_batas_tidak_bisa_dibayar()
    {
        $pesanan = $this->buatPesanan();
        $this->actingAs($this->user);
        $this->travel(25)->hours();

        Livewire::test(DetailPesanan::class, ['pesanan' => $pesanan])
            ->assertDontSee('Bayar sekarang')
            ->call('bayar')
            ->assertNotDispatched('buka-pembayaran');
    }

    public function test_pesanan_pelanggan_lain_tidak_bisa_dibuka()
    {
        $pesanan = $this->buatPesanan();
        $this->actingAs(User::factory()->create());

        $this->get(route('pesanan.detail', $pesanan))->assertNotFound();
        $this->get(route('pembayaran.simulasi', $pesanan))->assertNotFound();
        $this->post(route('pembayaran.simulasi', $pesanan), ['status' => 'settlement'])->assertNotFound();

        $this->assertSame(StatusPesanan::MenungguPembayaran, $pesanan->fresh()->status);
    }

    public function test_simulasi_pembayaran_melewati_jalur_notifikasi()
    {
        $pesanan = $this->buatPesanan();
        $this->actingAs($this->user);

        $this->get(route('pembayaran.simulasi', $pesanan))->assertOk()->assertSee('Rp 108.000');
        $this->post(route('pembayaran.simulasi', $pesanan), ['status' => 'settlement'])
            ->assertRedirect(route('pesanan.detail', $pesanan));

        $this->assertSame(StatusPesanan::Dibayar, $pesanan->fresh()->status);
        $this->get(route('pesanan.detail', $pesanan))->assertSee('Dibayar')->assertDontSee('Bayar sekarang');
    }

    public function test_simulasi_tidak_tersedia_saat_memakai_midtrans()
    {
        config(['pembayaran.driver' => 'midtrans']);
        $pesanan = $this->buatPesanan();
        $this->actingAs($this->user);

        $this->post(route('pembayaran.simulasi', $pesanan), ['status' => 'settlement'])->assertNotFound();
        $this->assertSame(StatusPesanan::MenungguPembayaran, $pesanan->fresh()->status);
    }

    public function test_simulasi_menolak_berjalan_di_production()
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->expectException(RuntimeException::class);

        app(PaymentGateway::class);
    }

    protected function kirimPesanan(string $resi): Pesanan
    {
        $pesanan = $this->buatPesanan();
        $pesanan->ubahStatus(StatusPesanan::Dibayar);
        $pesanan->ubahStatus(StatusPesanan::Diproses);
        $pesanan->kirim($resi);

        return $pesanan->fresh();
    }

    public function test_pelanggan_melihat_resi_dan_riwayat_pelacakan()
    {
        $pesanan = $this->kirimPesanan('JNE0012345678');
        $this->actingAs($this->user);

        $this->get(route('pesanan.detail', $pesanan))->assertOk()
            ->assertSee('JNE0012345678')
            ->assertSee('Lacak paket');

        Livewire::test(DetailPesanan::class, ['pesanan' => $pesanan])
            ->call('muatPelacakan')
            ->assertSee('Paket dalam perjalanan')
            ->assertSeeInOrder(['Paket dalam perjalanan ke kota tujuan', 'Paket diterima di gudang JNE Bandung']);

        $this->assertSame('Paket dalam perjalanan', $pesanan->pengiriman->fresh()->status_lacak);
        $this->assertSame(StatusPesanan::Dikirim, $pesanan->fresh()->status);
    }

    public function test_paket_yang_dilacak_sudah_diterima_membuat_pesanan_selesai()
    {
        $pesanan = $this->kirimPesanan('SAMPAI001');
        $this->actingAs($this->user);

        Livewire::test(DetailPesanan::class, ['pesanan' => $pesanan])
            ->call('muatPelacakan')
            ->assertSee('Paket diterima')
            ->assertSee('Selesai');

        $pesanan->refresh();
        $this->assertSame(StatusPesanan::Selesai, $pesanan->status);
        $this->assertNotNull($pesanan->selesai_at);
        $this->assertNotNull($pesanan->pengiriman->diterima_at);

        // pelacakan berikutnya tidak mencoba menyelesaikan pesanan dua kali
        Livewire::test(DetailPesanan::class, ['pesanan' => $pesanan])->call('muatPelacakan')->assertHasNoErrors();
    }

    public function test_pelacakan_yang_gagal_dimuat_tetap_menampilkan_resi()
    {
        $pesanan = $this->kirimPesanan('GAGAL001');
        $this->actingAs($this->user);

        Livewire::test(DetailPesanan::class, ['pesanan' => $pesanan])
            ->call('muatPelacakan')
            ->assertSee('GAGAL001')
            ->assertSee('Riwayat pelacakan belum bisa dimuat.');

        $this->assertSame(StatusPesanan::Dikirim, $pesanan->fresh()->status);
    }

    public function test_pesanan_yang_belum_dikirim_tidak_dilacak()
    {
        $pesanan = $this->buatPesanan();
        $this->actingAs($this->user);

        Livewire::test(DetailPesanan::class, ['pesanan' => $pesanan])
            ->assertDontSee('Lacak paket')
            ->call('muatPelacakan')
            ->assertSet('pelacakan', null);
    }

    public function test_penjadwal_melacak_paket_dan_menyelesaikan_yang_sudah_diterima()
    {
        $this->varian->update(['stok' => 10]);
        $sampai = $this->kirimPesanan('SAMPAI002');
        $diJalan = $this->kirimPesanan('JNE0099887766');
        $gagal = $this->kirimPesanan('GAGAL002');

        $this->artisan('pengiriman:lacak')->assertSuccessful();

        $this->assertSame(StatusPesanan::Selesai, $sampai->fresh()->status);
        $this->assertSame(StatusPesanan::Dikirim, $diJalan->fresh()->status);
        $this->assertSame(StatusPesanan::Dikirim, $gagal->fresh()->status);
    }

    public function test_pelacakan_dibatasi_supaya_kurir_tidak_dipanggil_terus_menerus()
    {
        $pesanan = $this->kirimPesanan('GAGAL003');
        $this->actingAs($this->user);
        $halaman = Livewire::test(DetailPesanan::class, ['pesanan' => $pesanan]);

        foreach (range(1, 10) as $_) {
            $halaman->call('muatPelacakan')->assertSee('Riwayat pelacakan belum bisa dimuat.');
        }

        $halaman->call('muatPelacakan')->assertSee('Terlalu sering memuat pelacakan.');
    }
}

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
}

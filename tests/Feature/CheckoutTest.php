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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

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
        $user->alamats()->create([
            'label' => 'Rumah',
            'penerima' => 'Budi Santoso',
            'no_hp' => '081234567890',
            'alamat' => 'Jl. Merdeka 1, Bandung',
            'kode_pos' => '40111',
            'utama' => true,
        ]);

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
            ->call('bayar')
            ->assertRedirect(route('pesanan.detail', 'SMP-20261026-0001').'?bayar=1');

        $pesanan = Pesanan::with('items', 'pembayaran')->firstOrFail();
        $this->assertSame(StatusPesanan::MenungguPembayaran, $pesanan->status);
        $this->assertSame([90000, 0, 0, 90000], [$pesanan->subtotal, $pesanan->ongkir, $pesanan->diskon, $pesanan->total]);
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

        $halaman = Livewire::test(Checkout::class);
        $varian->update(['harga' => 50000]);
        $halaman->call('bayar');

        $this->assertSame(50000, Pesanan::firstOrFail()->total);
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
        Livewire::test(Checkout::class)->call('bayar')->assertHasNoErrors();

        Auth::logout();
        $this->actingAs($kedua);
        Livewire::test(Checkout::class)
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
        Pesanan::buatDariKeranjang($keranjang, $user->alamats->first());

        $this->expectException(KeranjangDitolak::class);

        try {
            Pesanan::buatDariKeranjang($keranjang, $user->alamats->first());
        } finally {
            $this->assertSame(1, Pesanan::count());
        }
    }

    public function test_halaman_pesanan_sesudah_checkout_langsung_membuka_jendela_pembayaran()
    {
        $this->actingAs($this->pelangganDenganAlamat());
        Keranjang::milikPengunjungAtauBaru()->tambah($this->buatVarian(), 1);
        Livewire::test(Checkout::class)->call('bayar');
        $pesanan = Pesanan::firstOrFail();

        $this->get(route('pesanan.detail', $pesanan).'?bayar=1')->assertOk();

        $this->assertStringStartsWith('simulasi-', $pesanan->fresh()->pembayaran->token);
        Livewire::withQueryParams(['bayar' => 1])->test(DetailPesanan::class, ['pesanan' => $pesanan])
            ->assertSet('bukaOtomatis.token', $pesanan->fresh()->pembayaran->token);
    }
}

<?php

namespace Tests\Feature;

use App\Livewire\Admin\DaftarProduk;
use App\Livewire\Checkout;
use App\Livewire\Navbar;
use App\Livewire\Pesananmasuk;
use App\Livewire\ProdukDetail;
use App\Models\admin;
use App\Models\Keranjang;
use App\Models\pesanan;
use App\Models\Produk;
use App\Models\User;
use App\Models\Varian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class TokoTest extends TestCase
{
    use RefreshDatabase;

    protected admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->admin = admin::create([
            'name' => 'administrator',
            'email' => 'admin@example.com',
            'password' => bcrypt('rahasia123'),
        ]);
    }

    protected function buatVarian(array $data = []): Varian
    {
        $produk = Produk::factory()->create(['nama' => 'Semprong Coklat', 'slug' => 'semprong-coklat-'.Produk::count()]);

        return Varian::factory()->for($produk)->create(array_merge(['nama' => 'Toples kecil', 'harga' => 15000, 'stok' => 10], $data));
    }

    public function test_halaman_publik_bisa_dibuka()
    {
        $varian = $this->buatVarian();

        $this->get('/')->assertOk()->assertSee('Semprong Coklat')
            ->assertSee('<title>Semprong Shop', false)
            ->assertDontSee('SemprongLoves');
        $this->get('/produk')->assertOk()->assertSee('Semprong Coklat');
        $this->get(route('produk.detail', $varian->produk->slug))->assertOk()->assertSee('Rp 15.000');
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
        $this->get('/admin/login')->assertOk();
    }

    public function test_registrasi_lalu_login_ulang()
    {
        $this->post('/register', [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect('/');

        $user = User::where('email', 'budi@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        // RegisterController sudah Hash::make; cast 'hashed' tidak boleh meng-hash dua kali
        $this->assertTrue(Hash::check('rahasia123', $user->password));

        $this->post('/logout');
        $this->assertGuest();

        $this->post('/login', ['email' => 'budi@example.com', 'password' => 'rahasia123'])->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_yang_sudah_login_tidak_bisa_buka_login_dan_register()
    {
        $this->actingAs(User::factory()->create());

        $this->get('/login')->assertRedirect('/');
        $this->get('/register')->assertRedirect('/');
    }

    public function test_tamu_diarahkan_ke_login_saat_checkout_dan_riwayat()
    {
        $this->get('/tas-belanja')->assertOk();
        $this->get('/checkout')->assertRedirect(route('login'));
        $this->get('/history')->assertRedirect(route('login'));
    }

    public function test_pencarian_produk_admin_kembali_ke_halaman_pertama()
    {
        Produk::factory()->count(13)->create();

        Livewire::test(DaftarProduk::class)
            ->call('gotoPage', 2)
            ->assertSet('paginators.page', 2)
            ->set('cari', 'Semprong')
            ->assertSet('paginators.page', 1);
    }

    public function test_alur_pesan_sampai_checkout()
    {
        $this->travelTo(now()->setDate(2026, 10, 22)->setTime(9, 30));

        $user = User::factory()->create();
        $varian = $this->buatVarian();

        $this->actingAs($user);

        Livewire::test(ProdukDetail::class, ['slug' => $varian->produk->slug])
            ->set('jumlah', 2)
            ->call('tambahKeKeranjang')
            ->assertDispatched('keranjang-diperbarui');

        $this->get('/tas-belanja')->assertOk()->assertSee('Rp 30.000');

        Livewire::test(Checkout::class)
            ->assertSet('total_harga', 30000)
            ->set('nohp', '081234567890')
            ->set('alamat', 'Jl. Merdeka 1')
            ->call('checkout')
            ->assertRedirect(route('history'));

        $pesanan = pesanan::where('id_user', $user->id)->firstOrFail();
        $this->assertSame('1', (string) $pesanan->status);
        $this->assertSame(30000, (int) $pesanan->total_harga);
        $this->assertSame('SP-'.$pesanan->id, $pesanan->kode_pemesanan);
        $this->assertSame(8, $varian->fresh()->stok);
        $this->assertSame('Jl. Merdeka 1', $user->fresh()->alamat);
        Livewire::test(Navbar::class)->assertSee('Tas (0)');

        $this->get('/history')->assertOk()
            ->assertSee('Belum Lunas')
            ->assertSee('22 Okt 2026, 09:30')
            ->assertSee('Toples kecil')
            ->assertSee('Rp 30.000');
    }

    public function test_checkout_ditolak_bila_stok_tidak_lagi_cukup()
    {
        $user = User::factory()->create();
        $varian = $this->buatVarian(['stok' => 3]);
        $this->actingAs($user);
        Keranjang::milikPengunjungAtauBaru()->tambah($varian, 3);
        $varian->update(['stok' => 2]);

        Livewire::test(Checkout::class)
            ->set('nohp', '081234567890')
            ->set('alamat', 'Jl. Merdeka 1')
            ->call('checkout')
            ->assertNoRedirect()
            ->assertSee('Stok Toples kecil tinggal 2.');

        $this->assertSame(0, pesanan::count());
        $this->assertSame(2, $varian->fresh()->stok);
    }

    public function test_checkout_dengan_tas_kosong_kembali_ke_tas_belanja()
    {
        $this->actingAs(User::factory()->create());

        $this->get('/checkout')->assertRedirect(route('keranjang'));
    }

    protected function isiKeranjang(User $user): void
    {
        $this->actingAs($user);
        Keranjang::milikPengunjungAtauBaru()->tambah($this->buatVarian(), 1);
    }

    public function test_checkout_menolak_nomor_hp_yang_terlalu_panjang()
    {
        $this->isiKeranjang($user = User::factory()->create());

        Livewire::test(Checkout::class)
            ->set('nohp', '08515591224124')
            ->set('alamat', 'Jl. Merdeka 1')
            ->call('checkout')
            ->assertHasErrors('nohp');

        $this->assertSame(0, pesanan::count());
    }

    public function test_checkout_menerima_nomor_hp_dengan_spasi_dan_tanda_hubung()
    {
        $this->isiKeranjang($user = User::factory()->create());

        Livewire::test(Checkout::class)
            ->set('nohp', '0812-345 678')
            ->set('alamat', 'Jl. Merdeka 1')
            ->call('checkout')
            ->assertHasNoErrors()
            ->assertRedirect(route('history'));

        $this->assertSame('0812345678', $user->fresh()->notelp);
        $this->assertSame('1', (string) pesanan::firstOrFail()->status);
    }

    public function test_halaman_admin_bisa_dibuka()
    {
        $varian = $this->buatVarian();

        $this->actingAs($this->admin, 'adminMiddle');

        $this->get('/admin/dashboard')->assertOk()->assertSee('Selamat Datang, Admin');
        $this->get('/admin/pesananmasuk')->assertOk();
        $this->get('/admin/riwayatpesanan')->assertOk();
        $this->get('/admin/kategori')->assertOk();
        $this->get('/admin/produk')->assertOk()->assertSee('Semprong Coklat');
        $this->get(route('admin.produk.ubah', $varian->produk))->assertOk()->assertSee('Toples kecil');
        $this->get('/admin/produk/baru')->assertOk();
    }

    public function test_login_admin()
    {
        $this->post('/admin/login', ['email' => 'admin@example.com', 'password' => 'rahasia123'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($this->admin, 'adminMiddle');
    }

    public function test_admin_konfirmasi_pesanan()
    {
        $user = User::factory()->create();
        // 'status' tidak ada di $fillable model pesanan
        $pesanan = pesanan::create(['id_user' => $user->id, 'total_harga' => 15000]);
        $pesanan->status = 1;
        $pesanan->save();

        Livewire::test(Pesananmasuk::class)
            ->assertSee($user->name)
            ->call('konfirmasi', $pesanan->id);

        $this->assertSame('2', (string) $pesanan->fresh()->status);
    }
}

<?php

namespace Tests\Feature;

use App\Livewire\Admin\DaftarProduk;
use App\Livewire\Checkout;
use App\Livewire\Navbar;
use App\Livewire\ProdukDetail;
use App\Models\admin;
use App\Models\Pesanan;
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
        $this->get('/pesanan')->assertRedirect(route('login'));
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

    public function test_alur_belanja_sampai_dibayar_dan_masuk_ke_admin()
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

        $checkout = Livewire::test(Checkout::class)->assertSee('Alamat baru');
        foreach (['label' => 'Rumah', 'penerima' => 'Budi', 'no_hp' => '081234567890', 'alamat_lengkap' => 'Jl. Merdeka 1'] as $kolom => $nilai) {
            $checkout->set("form.$kolom", $nilai);
        }
        $checkout->set('form.area', ['id' => 'SIMULASI-40115', 'nama' => 'Bandung Wetan, Bandung, Jawa Barat. 40115'])
            ->call('simpanAlamat')->assertHasNoErrors()
            ->assertSee('SiCepat')
            ->set('kurir', 'sicepat:reg')
            ->call('bayar');

        $pesanan = Pesanan::firstOrFail();
        $this->assertSame('SMP-20261022-0001', $pesanan->nomor);
        $this->assertSame(8, $varian->fresh()->stok);
        Livewire::test(Navbar::class)->assertSee('Tas (0)');

        $this->post(route('pembayaran.simulasi', $pesanan), ['status' => 'settlement']);
        $this->get('/pesanan')->assertOk()->assertSee('Dibayar')->assertSee('Rp 46.500'); // 30.000 + SiCepat 16.500

        $this->actingAs($this->admin, 'adminMiddle');
        $this->get('/admin/pesanan?status=dibayar')->assertOk()->assertSee('SMP-20261022-0001')->assertSee('Toples kecil');
        $this->get('/admin/dashboard')->assertOk()->assertSeeInOrder(['Omzet hari ini', 'Rp 30.000']); // tanpa ongkir
    }

    public function test_halaman_admin_bisa_dibuka()
    {
        $varian = $this->buatVarian();

        $this->actingAs($this->admin, 'adminMiddle');

        $this->get('/admin/dashboard')->assertOk()->assertSee('Selamat Datang, Admin');
        $this->get('/admin/pesanan')->assertOk();
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
}

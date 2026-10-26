<?php

namespace Tests\Feature;

use App\Livewire\Admin\DaftarProduk;
use App\Livewire\Checkout;
use App\Livewire\Navbar;
use App\Livewire\Pesananmasuk;
use App\Livewire\Pesanann;
use App\Livewire\ProdukDetail;
use App\Models\admin;
use App\Models\pesanan;
use App\Models\pesanandetail;
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

    public function test_tamu_diarahkan_ke_login()
    {
        $this->get('/pesanann')->assertRedirect(route('login'));
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
            ->set('jumlah_pesanan', 2)
            ->call('masukkankeranjang')
            ->assertDispatched('masukKeranjang')
            ->assertSee('Semprong masuk ke tas belanja.');

        $pesanan = pesanan::where('id_user', $user->id)->where('status', 0)->firstOrFail();
        $this->assertSame(30000, (int) $pesanan->total_harga);
        $this->assertSame('SP-'.$pesanan->id, $pesanan->kode_pemesanan);
        $this->assertSame(8, $varian->fresh()->stok);

        Livewire::test(Navbar::class)->assertSet('jumlah', 1);

        $this->get('/pesanann')->assertOk()->assertSee('Rp 30.000');

        Livewire::test(Checkout::class)
            ->assertSet('total_harga', 30000)
            ->set('nohp', '081234567890')
            ->set('alamat', 'Jl. Merdeka 1')
            ->call('checkout')
            ->assertRedirect(route('history'));

        $this->assertSame('1', (string) $pesanan->fresh()->status);
        $this->assertSame('Jl. Merdeka 1', $user->fresh()->alamat);

        $this->get('/history')->assertOk()
            ->assertSee('Belum Lunas')
            ->assertSee('22 Okt 2026, 09:30')
            ->assertSee('Rp 30.000');
    }

    public function test_masukkan_ke_tas_menolak_jumlah_melebihi_stok_varian()
    {
        $varian = $this->buatVarian(['stok' => 3]);
        $this->actingAs(User::factory()->create());

        Livewire::test(ProdukDetail::class, ['slug' => $varian->produk->slug])
            ->set('jumlah_pesanan', 4)
            ->call('masukkankeranjang')
            ->assertHasErrors('jumlah_pesanan')
            ->assertSee('Stok Toples kecil tinggal 3.');

        $this->assertSame(3, $varian->fresh()->stok);
        $this->assertSame(0, pesanan::count());
    }

    protected function buatPesananDiKeranjang(User $user): pesanan
    {
        $pesanan = pesanan::create(['id_user' => $user->id, 'total_harga' => 15000]);
        pesanandetail::create([
            'id_varian' => $this->buatVarian()->id,
            'id_pesanan' => $pesanan->id,
            'jumlah_pesanan' => 1,
            'total_harga' => 15000,
        ]);

        return $pesanan;
    }

    public function test_checkout_menolak_nomor_hp_yang_terlalu_panjang()
    {
        $user = User::factory()->create();
        $pesanan = $this->buatPesananDiKeranjang($user);
        $this->actingAs($user);

        Livewire::test(Checkout::class)
            ->set('nohp', '08515591224124')
            ->set('alamat', 'Jl. Merdeka 1')
            ->call('checkout')
            ->assertHasErrors('nohp');

        $this->assertSame('0', (string) $pesanan->fresh()->status);
    }

    public function test_checkout_menerima_nomor_hp_dengan_spasi_dan_tanda_hubung()
    {
        $user = User::factory()->create();
        $pesanan = $this->buatPesananDiKeranjang($user);
        $this->actingAs($user);

        Livewire::test(Checkout::class)
            ->set('nohp', '0812-345 678')
            ->set('alamat', 'Jl. Merdeka 1')
            ->call('checkout')
            ->assertHasNoErrors()
            ->assertRedirect(route('history'));

        $this->assertSame('0812345678', $user->fresh()->notelp);
        $this->assertSame('1', (string) $pesanan->fresh()->status);
    }

    public function test_hapus_item_pesanan()
    {
        $user = User::factory()->create();
        $pesanan = pesanan::create(['id_user' => $user->id, 'total_harga' => 15000, 'status' => 0]);
        $detail = pesanandetail::create([
            'id_varian' => $this->buatVarian()->id,
            'id_pesanan' => $pesanan->id,
            'jumlah_pesanan' => 1,
            'total_harga' => 15000,
        ]);

        $this->actingAs($user);

        Livewire::test(Pesanann::class)
            ->call('destroy', $detail->id)
            ->assertDispatched('masukKeranjang');

        $this->assertModelMissing($detail);
        $this->assertModelMissing($pesanan);
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

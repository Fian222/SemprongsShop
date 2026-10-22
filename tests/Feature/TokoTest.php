<?php

namespace Tests\Feature;

use App\Livewire\Checkout;
use App\Livewire\Navbar;
use App\Livewire\Pesananmasuk;
use App\Livewire\Pesanann;
use App\Livewire\Produkadmin;
use App\Livewire\ProdukDetail;
use App\Livewire\Produkdetailadmin;
use App\Livewire\Produkindex;
use App\Livewire\Tambahbarang;
use App\Models\admin;
use App\Models\barang;
use App\Models\pesanan;
use App\Models\pesanandetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

    protected function buatBarang(array $data = []): barang
    {
        return barang::create(array_merge([
            'id_admin' => $this->admin->id,
            'nama_barang' => 'Semprong Coklat',
            'harga_barang' => 15000,
            'stok_barang' => 10,
            'gambar' => 'coklat.jpg',
        ], $data));
    }

    public function test_halaman_publik_bisa_dibuka()
    {
        $barang = $this->buatBarang();

        $this->get('/')->assertOk()->assertSee('Semprong Coklat')
            ->assertSee('<title>Semprong Shop', false)
            ->assertDontSee('SemprongLoves');
        $this->get('/produk')->assertOk()->assertSee('Semprong Coklat');
        $this->get(route('produk.detail', $barang->id))->assertOk()->assertSee('Rp 15.000');
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

    public function test_pencarian_produk()
    {
        $this->buatBarang(['nama_barang' => 'Semprong Coklat']);
        $this->buatBarang(['nama_barang' => 'Semprong Keju']);

        Livewire::test(Produkindex::class)
            ->assertSee('Semprong Coklat')
            ->assertSee('Semprong Keju')
            ->set('search', 'Keju')
            ->assertSee('Semprong Keju')
            ->assertDontSee('Semprong Coklat');
    }

    public function test_pencarian_produk_admin_kembali_ke_halaman_pertama()
    {
        foreach (range(1, 7) as $i) {
            $this->buatBarang(['nama_barang' => 'Semprong '.$i]);
        }

        Livewire::test(Produkadmin::class)
            ->call('gotoPage', 2)
            ->assertSet('paginators.page', 2)
            ->set('search', 'Semprong')
            ->assertSet('paginators.page', 1);
    }

    public function test_alur_pesan_sampai_checkout()
    {
        $this->travelTo(now()->setDate(2026, 10, 22)->setTime(9, 30));

        $user = User::factory()->create();
        $barang = $this->buatBarang();

        $this->actingAs($user);

        Livewire::test(ProdukDetail::class, ['id' => $barang->id])
            ->set('jumlah_pesanan', 2)
            ->call('masukkankeranjang')
            ->assertDispatched('masukKeranjang')
            ->assertSee('Sukses Masuk Pesanan');

        $pesanan = pesanan::where('id_user', $user->id)->where('status', 0)->firstOrFail();
        $this->assertSame(30000, (int) $pesanan->total_harga);
        $this->assertSame('SP-'.$pesanan->id, $pesanan->kode_pemesanan);
        $this->assertSame(8, (int) $barang->fresh()->stok_barang);

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

    public function test_hapus_item_pesanan()
    {
        $user = User::factory()->create();
        $barang = $this->buatBarang();
        $pesanan = pesanan::create(['id_user' => $user->id, 'total_harga' => 15000, 'status' => 0]);
        $detail = pesanandetail::create([
            'id_barang' => $barang->id,
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
        $barang = $this->buatBarang();

        $this->actingAs($this->admin, 'adminMiddle');

        $this->get('/admin/dashboard')->assertOk()->assertSee('Selamat Datang, Admin');
        $this->get('/admin/pesananmasuk')->assertOk();
        $this->get('/admin/riwayatpesanan')->assertOk();
        $this->get('/admin/produkadmin')->assertOk()->assertSee('Semprong Coklat');
        $this->get(route('admin.detail', $barang->id))->assertOk();
        $this->get('/admin/tambahbarang')->assertOk();
    }

    public function test_login_admin()
    {
        $this->post('/admin/login', ['email' => 'admin@example.com', 'password' => 'rahasia123'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($this->admin, 'adminMiddle');
    }

    public function test_admin_tambah_barang()
    {
        Storage::fake('public_uploads');

        $this->actingAs($this->admin, 'adminMiddle');

        Livewire::test(Tambahbarang::class)
            ->set('nama', 'Semprong Pandan')
            ->set('harga', 12000)
            ->set('stok', 5)
            ->set('gambar', UploadedFile::fake()->image('pandan.jpg'))
            ->call('tambah')
            ->assertHasNoErrors();

        Storage::disk('public_uploads')->assertExists('images/Semprong Pandan.jpg');
        $this->assertDatabaseHas('barangs', ['nama_barang' => 'Semprong Pandan', 'id_admin' => $this->admin->id]);
    }

    public function test_admin_ubah_stok_dan_harga()
    {
        $barang = $this->buatBarang();

        Livewire::test(Produkdetailadmin::class, ['id' => $barang->id])
            ->set('jumlah_tambah', 5)
            ->set('ubah_harga', 20000)
            ->call('edit', $barang->id)
            ->assertSee('Rp 20.000');

        $this->assertSame(15, (int) $barang->fresh()->stok_barang);
        $this->assertSame(20000, (int) $barang->fresh()->harga_barang);
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

<?php

namespace Tests\Feature;

use App\Livewire\Admin\DaftarProduk;
use App\Livewire\Admin\FormProduk;
use App\Livewire\Admin\GaleriProduk;
use App\Livewire\Admin\KelolaKategori;
use App\Livewire\Admin\VarianProduk;
use App\Models\admin;
use App\Models\FotoProduk;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Varian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class KatalogAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->actingAs(admin::create([
            'name' => 'administrator',
            'email' => 'admin@example.com',
            'password' => bcrypt('rahasia123'),
        ]), 'adminMiddle');
    }

    public function test_admin_membuat_mengubah_dan_menonaktifkan_kategori()
    {
        $this->get('/admin/kategori')->assertOk();

        Livewire::test(KelolaKategori::class)
            ->set('form.nama', 'Semprong Isi')
            ->set('form.urutan', 2)
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSee('Semprong Isi');

        $kategori = Kategori::where('slug', 'semprong-isi')->firstOrFail();
        $this->assertSame(2, $kategori->urutan);
        $this->assertTrue($kategori->aktif);

        Livewire::test(KelolaKategori::class)
            ->call('ubah', $kategori->id)
            ->assertSet('form.nama', 'Semprong Isi')
            ->set('form.nama', 'Semprong Berisi')
            ->set('form.slug', 'berisi')
            ->set('form.aktif', false)
            ->call('simpan')
            ->assertHasNoErrors();

        $kategori->refresh();
        $this->assertSame('Semprong Berisi', $kategori->nama);
        $this->assertSame('berisi', $kategori->slug);
        $this->assertFalse($kategori->aktif);
    }

    public function test_slug_kategori_harus_unik()
    {
        Kategori::factory()->create(['slug' => 'hampers']);

        Livewire::test(KelolaKategori::class)
            ->set('form.nama', 'Hampers')
            ->call('simpan')
            ->assertHasErrors('form.slug');

        $this->assertSame(1, Kategori::count());
    }

    public function test_admin_membuat_produk_lalu_diarahkan_ke_halaman_ubah()
    {
        $kategori = Kategori::factory()->create(['nama' => 'Semprong Original']);

        $this->get('/admin/produk/baru')->assertOk();

        Livewire::test(FormProduk::class)
            ->set('form.kategori_id', $kategori->id)
            ->set('form.nama', 'Semprong Gula Aren')
            ->set('form.deskripsi', 'Manis legit gula aren.')
            ->set('form.komposisi', 'Tepung beras, gula aren')
            ->set('form.unggulan', true)
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.produk.ubah', Produk::firstOrFail()));

        $produk = Produk::firstOrFail();
        $this->assertSame('semprong-gula-aren', $produk->slug);
        $this->assertTrue($produk->aktif);
        $this->assertTrue($produk->unggulan);
        $this->assertSame('Tepung beras, gula aren', $produk->komposisi);
    }

    public function test_admin_mengubah_dan_menonaktifkan_produk()
    {
        $produk = Produk::factory()->create(['nama' => 'Semprong Keju']);

        $this->get(route('admin.produk.ubah', $produk))->assertOk()->assertSee('Semprong Keju');

        Livewire::test(FormProduk::class, ['produk' => $produk])
            ->assertSet('form.nama', 'Semprong Keju')
            ->set('form.nama', 'Semprong Keju Edam')
            ->set('form.aktif', false)
            ->call('simpan')
            ->assertHasNoErrors();

        $produk->refresh();
        $this->assertSame('Semprong Keju Edam', $produk->nama);
        $this->assertFalse($produk->aktif);
    }

    public function test_produk_wajib_punya_nama_kategori_dan_deskripsi()
    {
        Livewire::test(FormProduk::class)
            ->call('simpan')
            ->assertHasErrors(['form.nama', 'form.kategori_id', 'form.deskripsi']);

        $this->assertSame(0, Produk::count());
    }

    public function test_daftar_produk_admin_juga_menampilkan_produk_nonaktif()
    {
        $produk = Produk::factory()->nonaktif()->create(['nama' => 'Semprong Tersembunyi']);
        Varian::factory()->for($produk)->create(['harga' => 45000]);
        Produk::factory()->create(['nama' => 'Semprong Lain']);

        $this->get('/admin/produk')->assertOk()
            ->assertSee('Semprong Tersembunyi')
            ->assertSee('Nonaktif')
            ->assertSee('Rp 45.000');

        Livewire::test(DaftarProduk::class)
            ->set('cari', 'Lain')
            ->assertSee('Semprong Lain')
            ->assertDontSee('Semprong Tersembunyi');
    }

    public function test_admin_menambah_mengubah_dan_menonaktifkan_varian()
    {
        $produk = Produk::factory()->create();

        Livewire::test(VarianProduk::class, ['produk' => $produk])
            ->set('form.nama', 'Toples kecil')
            ->set('form.sku', 'SMP-PDN-250')
            ->set('form.harga', 45000)
            ->set('form.harga_coret', 55000)
            ->set('form.stok', 12)
            ->set('form.berat', 300)
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSee('SMP-PDN-250');

        $varian = $produk->varians()->firstOrFail();
        $this->assertSame(['Toples kecil', 45000, 55000, 12, 300, true],
            [$varian->nama, $varian->harga, $varian->harga_coret, $varian->stok, $varian->berat, $varian->aktif]);

        Livewire::test(VarianProduk::class, ['produk' => $produk])
            ->call('ubah', $varian->id)
            ->assertSet('form.sku', 'SMP-PDN-250')
            ->set('form.harga', 40000)
            ->set('form.harga_coret', '')
            ->set('form.aktif', false)
            ->call('simpan')
            ->assertHasNoErrors();

        $varian->refresh();
        $this->assertSame(40000, $varian->harga);
        $this->assertNull($varian->harga_coret);
        $this->assertFalse($varian->aktif);

        $this->get(route('admin.produk.ubah', $produk))->assertOk()->assertSee('SMP-PDN-250');
    }

    public function test_varian_menolak_sku_ganda_dan_harga_coret_yang_tidak_lebih_mahal()
    {
        Varian::factory()->create(['sku' => 'SMP-001']);
        $produk = Produk::factory()->create();

        Livewire::test(VarianProduk::class, ['produk' => $produk])
            ->set('form.nama', 'Toples kecil')
            ->set('form.sku', 'SMP-001')
            ->set('form.harga', 45000)
            ->set('form.harga_coret', 45000)
            ->set('form.stok', 5)
            ->set('form.berat', 300)
            ->call('simpan')
            ->assertHasErrors(['form.sku', 'form.harga_coret']);

        $this->assertSame(0, $produk->varians()->count());
    }

    public function test_admin_tidak_bisa_mengubah_varian_milik_produk_lain()
    {
        $produk = Produk::factory()->create();
        $varianLain = Varian::factory()->create();

        Livewire::test(VarianProduk::class, ['produk' => $produk])
            ->call('ubah', $varianLain->id)
            ->assertNotFound();
    }

    public function test_admin_mengunggah_mengurutkan_dan_menghapus_foto()
    {
        Storage::fake(FotoProduk::DISK);
        $produk = Produk::factory()->create(['slug' => 'semprong-pandan']);
        Varian::factory()->for($produk)->create();

        Livewire::test(GaleriProduk::class, ['produk' => $produk])
            ->set('unggahan', [UploadedFile::fake()->image('depan.jpg'), UploadedFile::fake()->image('samping.png')])
            ->call('unggah')
            ->assertHasNoErrors();

        [$depan, $samping] = $produk->fotos()->get();
        Storage::disk(FotoProduk::DISK)->assertExists([$depan->path, $samping->path]);

        Livewire::test(GaleriProduk::class, ['produk' => $produk])
            ->call('geser', $samping->id, -1);

        $this->assertSame([$samping->id, $depan->id], $produk->fotos()->pluck('id')->all());
        $this->get('/produk/semprong-pandan')->assertOk()
            ->assertSeeInOrder([$samping->url(), $depan->url()]);

        Livewire::test(GaleriProduk::class, ['produk' => $produk])
            ->call('hapus', $samping->id);

        $this->assertSame([$depan->id], $produk->fotos()->pluck('id')->all());
        Storage::disk(FotoProduk::DISK)->assertMissing($samping->path);
    }

    public function test_unggahan_foto_harus_berupa_gambar()
    {
        Storage::fake(FotoProduk::DISK);
        $produk = Produk::factory()->create();

        Livewire::test(GaleriProduk::class, ['produk' => $produk])
            ->set('unggahan', [UploadedFile::fake()->create('katalog.pdf', 100, 'application/pdf')])
            ->call('unggah')
            ->assertHasErrors('unggahan.0');

        $this->assertSame(0, $produk->fotos()->count());
    }
}

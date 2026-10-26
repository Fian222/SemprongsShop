<?php

namespace Tests\Feature;

use App\Livewire\Katalog;
use App\Livewire\ProdukDetail;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Varian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KatalogTokoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    protected function buatProduk(array $data = [], array $varians = [['harga' => 45000]]): Produk
    {
        $produk = Produk::factory()->create($data);
        foreach ($varians as $varian) {
            Varian::factory()->for($produk)->create($varian);
        }

        return $produk;
    }

    public function test_kartu_produk_menampilkan_harga_mulai_dari_varian_aktif_termurah()
    {
        $this->buatProduk(['nama' => 'Semprong Wijen'], [
            ['nama' => 'Toples besar', 'harga' => 85000],
            ['nama' => 'Toples kecil', 'harga' => 45000],
            ['nama' => 'Sachet', 'harga' => 15000, 'aktif' => false],
        ]);

        $this->get('/produk')->assertOk()
            ->assertSee('Semprong Wijen')
            ->assertSee('mulai Rp 45.000')
            ->assertDontSee('Rp 15.000');
    }

    public function test_produk_nonaktif_dan_tanpa_varian_aktif_tidak_tampil_di_toko()
    {
        $this->buatProduk(['nama' => 'Semprong Tampil']);
        $this->buatProduk(['nama' => 'Semprong Disembunyikan', 'aktif' => false]);
        $this->buatProduk(['nama' => 'Semprong Tanpa Varian'], [['aktif' => false]]);
        $this->buatProduk([
            'nama' => 'Semprong Kategori Mati',
            'kategori_id' => Kategori::factory()->create(['aktif' => false])->id,
        ]);

        $this->get('/produk')->assertOk()
            ->assertSee('Semprong Tampil')
            ->assertDontSee('Semprong Disembunyikan')
            ->assertDontSee('Semprong Tanpa Varian')
            ->assertDontSee('Semprong Kategori Mati');

        $this->get('/')->assertOk()
            ->assertSee('Semprong Tampil')
            ->assertDontSee('Semprong Disembunyikan');
    }

    public function test_filter_kategori_dibaca_dari_url()
    {
        $hampers = Kategori::factory()->create(['nama' => 'Hampers', 'slug' => 'hampers']);
        Kategori::factory()->create(['nama' => 'Kategori Tersembunyi', 'aktif' => false]);
        $this->buatProduk(['nama' => 'Hampers Lebaran', 'kategori_id' => $hampers->id]);
        $this->buatProduk(['nama' => 'Semprong Original']);

        $this->get('/produk?kategori=hampers')->assertOk()
            ->assertSee('Hampers Lebaran')
            ->assertDontSee('Semprong Original')
            ->assertDontSee('Kategori Tersembunyi');

        Livewire::test(Katalog::class)
            ->assertSee('Semprong Original')
            ->set('kategori', 'hampers')
            ->assertSee('Hampers Lebaran')
            ->assertDontSee('Semprong Original');
    }

    public function test_urutan_harga_dan_terbaru_dibaca_dari_url()
    {
        $this->buatProduk(['nama' => 'Semprong Sedang'], [['harga' => 50000]]);
        $this->buatProduk(['nama' => 'Semprong Mahal'], [['harga' => 90000], ['harga' => 20000, 'aktif' => false]]);
        $this->buatProduk(['nama' => 'Semprong Murah'], [['harga' => 30000]]);

        $this->get('/produk?urut=harga-terendah')->assertOk()
            ->assertSeeInOrder(['Semprong Murah', 'Semprong Sedang', 'Semprong Mahal']);
        $this->get('/produk?urut=harga-tertinggi')->assertOk()
            ->assertSeeInOrder(['Semprong Mahal', 'Semprong Sedang', 'Semprong Murah']);
        $this->get('/produk')->assertOk()
            ->assertSeeInOrder(['Semprong Murah', 'Semprong Mahal', 'Semprong Sedang']);

        Livewire::test(Katalog::class)
            ->set('urut', 'harga-terendah')
            ->assertSeeInOrder(['Semprong Murah', 'Semprong Sedang', 'Semprong Mahal']);
    }

    public function test_pencarian_dibaca_dari_url()
    {
        $this->buatProduk(['nama' => 'Semprong Coklat']);
        $this->buatProduk(['nama' => 'Semprong Keju']);

        $this->get('/produk?cari=keju')->assertOk()
            ->assertSee('Semprong Keju')
            ->assertDontSee('Semprong Coklat');

        Livewire::test(Katalog::class)
            ->set('cari', 'Coklat')
            ->assertSee('Semprong Coklat')
            ->assertDontSee('Semprong Keju')
            ->set('cari', 'Durian')
            ->assertSee('Tidak ada produk yang cocok dengan “Durian”')
            ->call('semuaProduk')
            ->assertSet('cari', '')
            ->assertSee('Semprong Keju');
    }

    public function test_detail_produk_menampilkan_deskripsi_komposisi_dan_varian_tersedia_termurah()
    {
        $this->buatProduk([
            'nama' => 'Semprong Pandan',
            'slug' => 'semprong-pandan',
            'deskripsi' => 'Gulungan tipis beraroma pandan.',
            'komposisi' => 'Tepung beras, santan, daun pandan',
        ], [
            ['nama' => 'Sachet', 'harga' => 15000, 'stok' => 0],
            ['nama' => 'Toples kecil', 'harga' => 45000, 'harga_coret' => 55000, 'stok' => 7, 'berat' => 300],
            ['nama' => 'Toples besar', 'harga' => 85000, 'stok' => 40],
            ['nama' => 'Kemasan lama', 'harga' => 60000, 'aktif' => false],
        ]);

        $this->get('/produk/semprong-pandan')->assertOk()
            ->assertSee('Semprong Pandan')
            ->assertSee('Gulungan tipis beraroma pandan.')
            ->assertSee('Tepung beras, santan, daun pandan')
            ->assertSee('Rp 45.000')
            ->assertSee('Rp 55.000')
            ->assertSee('Tinggal 7')
            ->assertSee('300 g')
            ->assertDontSee('Kemasan lama');
    }

    public function test_memilih_varian_memperbarui_harga_dan_stok_dan_varian_habis_tidak_bisa_dipilih()
    {
        $produk = $this->buatProduk(['slug' => 'semprong-wijen'], [
            ['nama' => 'Toples kecil', 'harga' => 45000, 'harga_coret' => 55000, 'stok' => 7],
            ['nama' => 'Toples besar', 'harga' => 85000, 'stok' => 40],
            ['nama' => 'Hampers', 'harga' => 150000, 'stok' => 0],
        ]);
        [$kecil, $besar, $hampers] = $produk->varians;

        Livewire::test(ProdukDetail::class, ['slug' => 'semprong-wijen'])
            ->assertSet('varianId', $kecil->id)
            ->assertSeeHtml('value="'.$hampers->id.'" disabled')
            ->set('varianId', $besar->id)
            ->assertSee('Rp 85.000')
            ->assertDontSee('Rp 55.000')
            ->assertSee('Stok 40')
            ->set('varianId', $hampers->id)
            ->assertSet('varianId', $besar->id);
    }

    public function test_varian_terpilih_yang_dinonaktifkan_saat_halaman_terbuka_diganti_varian_lain()
    {
        $produk = $this->buatProduk(['slug' => 'semprong-wijen'], [
            ['nama' => 'Toples kecil', 'harga' => 45000],
            ['nama' => 'Toples besar', 'harga' => 85000],
        ]);
        [$kecil, $besar] = $produk->varians;

        $halaman = Livewire::test(ProdukDetail::class, ['slug' => 'semprong-wijen'])
            ->assertSet('varianId', $kecil->id);

        $kecil->update(['aktif' => false]);

        $halaman->call('$refresh')
            ->assertOk()
            ->assertSee('Rp 85.000')
            ->assertSet('varianId', $besar->id);
    }

    public function test_detail_produk_yang_tidak_tampil_mengembalikan_404()
    {
        $this->buatProduk(['slug' => 'semprong-lama', 'aktif' => false]);

        $this->get('/produk/semprong-lama')->assertNotFound();
        $this->get('/produk/tidak-ada')->assertNotFound();
    }
}

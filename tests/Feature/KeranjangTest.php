<?php

namespace Tests\Feature;

use App\Livewire\KeranjangBelanja;
use App\Livewire\Navbar;
use App\Livewire\ProdukDetail;
use App\Models\Keranjang;
use App\Models\Produk;
use App\Models\User;
use App\Models\Varian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KeranjangTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    protected function buatVarian(array $data = [], string $produk = 'Semprong Pandan'): Varian
    {
        return Varian::factory()
            ->for(Produk::factory()->state(['nama' => $produk, 'slug' => str()->slug($produk)]))
            ->create(array_merge(['nama' => 'Toples kecil', 'harga' => 45000, 'stok' => 5], $data));
    }

    public function test_tamu_menambah_varian_ke_keranjang_tanpa_login_dan_stok_tidak_berubah()
    {
        $varian = $this->buatVarian();

        Livewire::test(ProdukDetail::class, ['slug' => 'semprong-pandan'])
            ->set('jumlah', 2)
            ->call('tambahKeKeranjang')
            ->assertHasNoErrors()
            ->assertDispatched('keranjang-diperbarui')
            ->assertSee('Masuk ke tas belanja');

        $this->assertGuest();
        $this->assertSame(5, $varian->fresh()->stok);

        $this->get('/tas-belanja')->assertOk()
            ->assertSee('Semprong Pandan')
            ->assertSee('Toples kecil')
            ->assertSee('Rp 90.000')
            ->assertSee('Tas (2)', false);
    }

    public function test_tas_belanja_mencoret_harga_varian_yang_punya_harga_coret()
    {
        Keranjang::milikPengunjungAtauBaru()->tambah($this->buatVarian(['harga_coret' => 55000]), 1);
        Keranjang::milikPengunjungAtauBaru()->tambah($this->buatVarian(['harga' => 30000, 'harga_coret' => 30000], 'Semprong Keju'), 1);

        Livewire::test(KeranjangBelanja::class)
            ->assertSeeHtml('<s class="tabular-nums"><span class="sr-only">Harga sebelumnya </span>Rp 55.000</s>')
            ->assertSeeHtmlInOrder(['Semprong Keju', 'Rp 30.000'])
            ->assertDontSeeHtml('Harga sebelumnya </span>Rp 30.000');
    }

    public function test_jumlah_melebihi_stok_ditolak_dengan_pesan_sisa_stok()
    {
        $this->buatVarian(['stok' => 3]);

        Livewire::test(ProdukDetail::class, ['slug' => 'semprong-pandan'])
            ->set('jumlah', 4)
            ->call('tambahKeKeranjang')
            ->assertHasErrors('jumlah')
            ->assertSee('Stok Toples kecil tinggal 3.')
            ->set('jumlah', 2)
            ->call('tambahKeKeranjang')
            ->assertHasNoErrors()
            ->set('jumlah', 2)
            ->call('tambahKeKeranjang')
            ->assertHasErrors('jumlah')
            ->assertSee('Stok Toples kecil tinggal 3, dan 2 sudah ada di tas belanjamu.');

        $this->get('/tas-belanja')->assertSee('Tas (2)', false);
    }

    public function test_mengubah_jumlah_dan_menghapus_item_memperbarui_subtotal_dan_ikon()
    {
        $pandan = $this->buatVarian(['harga' => 45000, 'stok' => 5]);
        $keju = $this->buatVarian(['harga' => 30000, 'stok' => 5], 'Semprong Keju');
        $keranjang = Keranjang::milikPengunjungAtauBaru();
        $itemPandan = $keranjang->tambah($pandan, 1);
        $itemKeju = $keranjang->tambah($keju, 1);

        Livewire::test(Navbar::class)->assertSee('Tas (2)')->assertSee('Rp 75.000');

        Livewire::test(KeranjangBelanja::class)
            ->assertSee('Rp 75.000')
            ->call('ubahJumlah', $itemPandan->id, 3)
            ->assertDispatched('keranjang-diperbarui')
            ->assertSee('Rp 165.000')
            ->call('ubahJumlah', $itemPandan->id, 6)
            ->assertHasErrors("jumlah.{$itemPandan->id}")
            ->assertSee('Stok Toples kecil tinggal 5.')
            ->call('hapus', $itemKeju->id)
            ->assertDispatched('keranjang-diperbarui')
            ->assertDontSee('Semprong Keju')
            ->assertSee('Rp 135.000');

        Livewire::test(Navbar::class)->assertSee('Tas (3)')->assertSee('Rp 135.000');

        Livewire::test(KeranjangBelanja::class)
            ->call('ubahJumlah', $itemPandan->id, 0)
            ->assertSee('Tas belanjamu masih kosong');

        $this->assertSame([5, 5], [$pandan->fresh()->stok, $keju->fresh()->stok]);
    }

    public function test_keranjang_tamu_digabung_ke_keranjang_akun_saat_login()
    {
        $pandan = $this->buatVarian(['stok' => 5]);
        $keju = $this->buatVarian(['stok' => 5], 'Semprong Keju');
        $user = User::factory()->create(['email' => 'budi@example.com', 'password' => 'rahasia123']);
        Keranjang::create(['user_id' => $user->id])->tambah($pandan, 4);

        $tamu = Keranjang::milikPengunjungAtauBaru();
        $tamu->tambah($pandan, 3);
        $tamu->tambah($keju, 1);

        $this->post('/login', ['email' => 'budi@example.com', 'password' => 'rahasia123'])->assertRedirect('/');

        $keranjang = Keranjang::firstWhere('user_id', $user->id);
        $this->assertSame(
            [$pandan->id => 5, $keju->id => 1],
            $keranjang->items()->pluck('jumlah', 'varian_id')->all(),
        );
        $this->assertModelMissing($tamu);
        $this->assertSame([5, 5], [$pandan->fresh()->stok, $keju->fresh()->stok]);
        $this->get('/tas-belanja')->assertSee('Tas (6)', false);
    }

    public function test_keranjang_tamu_ikut_ke_akun_baru_saat_mendaftar()
    {
        $tamu = Keranjang::milikPengunjungAtauBaru();
        $tamu->tambah($this->buatVarian(), 2);

        $this->post('/register', [
            'name' => 'Sari',
            'email' => 'sari@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect('/');

        $this->get('/tas-belanja')->assertSee('Semprong Pandan')->assertSee('Tas (2)', false);
    }

    public function test_jumlah_yang_bukan_angka_atau_negatif_ditolak_tanpa_menghapus_item()
    {
        $item = Keranjang::milikPengunjungAtauBaru()->tambah($this->buatVarian(), 2);

        foreach (['abc', '', '-1'] as $jumlah) {
            Livewire::test(KeranjangBelanja::class)
                ->call('ubahJumlah', $item->id, $jumlah)
                ->assertHasErrors("jumlah.{$item->id}");
        }

        $this->assertSame(2, $item->fresh()->jumlah);
    }

    public function test_varian_yang_tidak_dijual_lagi_tidak_bisa_ditambah_jumlahnya_dan_tidak_ikut_digabung()
    {
        $varian = $this->buatVarian();
        $item = Keranjang::milikPengunjungAtauBaru()->tambah($varian, 1);
        $varian->update(['aktif' => false]);

        Livewire::test(KeranjangBelanja::class)
            ->assertSee('Sedang tidak dijual')
            ->call('ubahJumlah', $item->id, 2)
            ->assertHasErrors("jumlah.{$item->id}")
            ->assertSee('sedang tidak dijual');
        $this->assertSame(1, $item->fresh()->jumlah);

        $user = User::factory()->create(['email' => 'budi@example.com', 'password' => 'rahasia123']);
        $this->post('/login', ['email' => 'budi@example.com', 'password' => 'rahasia123']);

        $this->assertSame(0, Keranjang::firstWhere('user_id', $user->id)->items()->count());
    }
}

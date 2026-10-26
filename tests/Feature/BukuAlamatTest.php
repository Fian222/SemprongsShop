<?php

namespace Tests\Feature;

use App\Livewire\BukuAlamat;
use App\Livewire\PilihArea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class BukuAlamatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    protected function isi($halaman, array $data = [])
    {
        foreach (array_merge([
            'label' => 'Rumah', 'penerima' => 'Budi Santoso', 'no_hp' => '0812-3456-7890',
            'alamat_lengkap' => 'Jl. Merdeka 1, Bandung',
            'area' => ['id' => 'SIMULASI-40115', 'nama' => 'Bandung Wetan, Bandung, Jawa Barat. 40115'],
        ], $data) as $kolom => $nilai) {
            $halaman->set("form.$kolom", $nilai);
        }

        return $halaman->call('simpan');
    }

    public function test_menambah_mengubah_menghapus_dan_menandai_alamat_utama()
    {
        $this->actingAs($user = User::factory()->create());
        $this->get('/akun/alamat')->assertOk();

        $halaman = Livewire::test(BukuAlamat::class)->call('tambah');
        $this->isi($halaman)->assertHasNoErrors();
        $halaman->call('tambah');
        $this->isi($halaman, ['label' => 'Kantor', 'alamat_lengkap' => 'Jl. Asia Afrika 8'])->assertHasNoErrors();

        [$rumah, $kantor] = $user->alamats()->oldest('id')->get();
        $this->assertTrue($rumah->utama, 'alamat pertama otomatis utama');
        $this->assertFalse($kantor->utama);
        $this->assertSame('081234567890', $rumah->no_hp);
        $this->assertSame(['SIMULASI-40115', 'Bandung Wetan, Bandung, Jawa Barat. 40115', '40115'], [$rumah->area_id, $rumah->area_nama, $rumah->kode_pos]);

        $halaman->call('jadikanUtama', $kantor->id);
        $this->assertSame([false, true], [$rumah->fresh()->utama, $kantor->fresh()->utama]);

        $halaman->call('ubah', $rumah->id)
            ->assertSet('form.penerima', 'Budi Santoso')
            ->assertSet('form.area', ['id' => 'SIMULASI-40115', 'nama' => 'Bandung Wetan, Bandung, Jawa Barat. 40115', 'kode_pos' => '40115']);
        $this->isi($halaman, ['penerima' => 'Sari Santoso', 'utama' => false])->assertHasNoErrors();
        $this->assertSame('Sari Santoso', $rumah->fresh()->penerima);

        $halaman->call('hapus', $kantor->id);
        $this->assertModelMissing($kantor);
        $this->assertTrue($rumah->fresh()->utama, 'alamat yang tersisa menjadi utama');
    }

    public function test_alamat_divalidasi()
    {
        $this->actingAs(User::factory()->create());

        $this->isi(Livewire::test(BukuAlamat::class)->call('tambah'), ['no_hp' => '12345', 'penerima' => '', 'area' => null])
            ->assertHasErrors(['form.no_hp', 'form.penerima', 'form.area']);
    }

    public function test_alamat_pelanggan_lain_tidak_bisa_diubah_atau_dihapus()
    {
        $alamatOrangLain = User::factory()->create()->alamats()->create([
            'label' => 'Rumah', 'penerima' => 'Orang lain', 'no_hp' => '081234567890',
            'alamat' => 'Jl. Lain', 'kode_pos' => '40111', 'utama' => true,
        ]);
        $this->actingAs(User::factory()->create());

        Livewire::test(BukuAlamat::class)->call('hapus', $alamatOrangLain->id)->assertNotFound();
        Livewire::test(BukuAlamat::class)->call('ubah', $alamatOrangLain->id)->assertNotFound();

        $this->assertModelExists($alamatOrangLain);
    }

    public function test_tamu_diarahkan_ke_login()
    {
        $this->get('/akun/alamat')->assertRedirect(route('login'));
        $this->get('/pesanan')->assertRedirect(route('login'));
    }

    public function test_area_tujuan_dicari_lalu_dipilih()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(PilihArea::class)
            ->set('cari', 'ba')
            ->assertDontSee('Bandung Wetan')
            ->set('cari', 'bandung')
            ->assertSee('Bandung Wetan, Bandung, Jawa Barat. 40115')
            ->assertSee('Coblong, Bandung, Jawa Barat. 40132')
            ->call('pilih', 1)
            ->assertSet('area', ['id' => 'SIMULASI-40132', 'nama' => 'Coblong, Bandung, Jawa Barat. 40132', 'kode_pos' => '40132'])
            ->assertSet('cari', '');
    }

    public function test_gangguan_pencarian_area_menampilkan_pesan()
    {
        config(['pengiriman.driver' => 'biteship', 'pengiriman.biteship.api_key' => 'biteship_test.UJI']);
        Http::fake(['api.biteship.com/*' => Http::response('Bad Gateway', 502)]);
        $this->actingAs(User::factory()->create());

        Livewire::test(PilihArea::class)
            ->set('cari', 'bandung')
            ->assertSee('Ongkir belum bisa dihitung saat ini.');
    }

    public function test_area_yang_tidak_dikenal_provider_ditolak()
    {
        $this->actingAs($user = User::factory()->create());

        $this->isi(Livewire::test(BukuAlamat::class)->call('tambah'), [
            'area' => ['id' => 'AREA-KARANGAN', 'nama' => 'Bandung Wetan, Bandung, Jawa Barat. 40115'],
        ])->assertHasErrors('form.area');

        $this->assertSame(0, $user->alamats()->count());
    }

    public function test_area_dipilih_dari_hasil_pencarian_terkunci()
    {
        $this->actingAs(User::factory()->create());

        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(PilihArea::class)->set('hasil', [['id' => 'AREA-KARANGAN', 'nama' => 'Murah sekali']]);
    }
}

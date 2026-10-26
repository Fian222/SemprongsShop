<?php

namespace Tests\Feature;

use App\Livewire\BukuAlamat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            'alamat_lengkap' => 'Jl. Merdeka 1, Bandung', 'kode_pos' => '40111',
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

        $halaman->call('jadikanUtama', $kantor->id);
        $this->assertSame([false, true], [$rumah->fresh()->utama, $kantor->fresh()->utama]);

        $halaman->call('ubah', $rumah->id)->assertSet('form.penerima', 'Budi Santoso');
        $this->isi($halaman, ['penerima' => 'Sari Santoso', 'utama' => false])->assertHasNoErrors();
        $this->assertSame('Sari Santoso', $rumah->fresh()->penerima);

        $halaman->call('hapus', $kantor->id);
        $this->assertModelMissing($kantor);
        $this->assertTrue($rumah->fresh()->utama, 'alamat yang tersisa menjadi utama');
    }

    public function test_alamat_divalidasi()
    {
        $this->actingAs(User::factory()->create());

        $this->isi(Livewire::test(BukuAlamat::class)->call('tambah'), ['no_hp' => '12345', 'kode_pos' => '401', 'penerima' => ''])
            ->assertHasErrors(['form.no_hp', 'form.kode_pos', 'form.penerima']);
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
}

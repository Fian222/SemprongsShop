<?php

namespace Tests\Feature;

use App\Models\admin;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAksesTest extends TestCase
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

    public function test_tamu_tidak_bisa_membuka_halaman_admin()
    {
        $produk = Produk::factory()->create();

        foreach (['dashboard', 'pesananmasuk', 'riwayatpesanan', 'kategori', 'produk', 'produk/baru', "produk/{$produk->id}/ubah"] as $halaman) {
            $this->get('/admin/'.$halaman)->assertRedirect(url('admin/login'));
        }
    }

    public function test_user_biasa_tidak_bisa_membuka_halaman_admin()
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin/dashboard')->assertRedirect(url('admin/login'));
    }

    public function test_halaman_login_admin_tetap_terbuka()
    {
        $this->get('/admin')->assertOk();
        $this->get('/admin/login')->assertOk();
    }

    public function test_aksi_livewire_admin_ditolak_setelah_logout()
    {
        $kategori = Kategori::factory()->create();

        $this->actingAs($this->admin, 'adminMiddle');
        $snapshot = $this->ambilSnapshot('/admin/kategori', 'admin.kelola-kategori');

        Auth::guard('adminMiddle')->logout();

        $this->kirimAksi($snapshot, 'ubah', [$kategori->id])->assertUnauthorized();
    }

    public function test_aksi_livewire_admin_berjalan_saat_login()
    {
        $kategori = Kategori::factory()->create();

        $this->actingAs($this->admin, 'adminMiddle');
        $snapshot = $this->ambilSnapshot('/admin/kategori', 'admin.kelola-kategori');

        $this->kirimAksi($snapshot, 'ubah', [$kategori->id])->assertOk();
    }

    public function test_aksi_varian_di_halaman_produk_ditolak_setelah_logout()
    {
        $produk = Produk::factory()->create();

        $this->actingAs($this->admin, 'adminMiddle');
        $snapshot = $this->ambilSnapshot(route('admin.produk.ubah', $produk), 'admin.varian-produk');

        Auth::guard('adminMiddle')->logout();

        $this->kirimAksi($snapshot, 'batal', [])->assertUnauthorized();
    }

    protected function ambilSnapshot(string $url, string $komponen): string
    {
        $html = $this->get($url)->assertOk()->getContent();

        preg_match('/wire:snapshot="([^"]*)"[^>]*wire:name="'.$komponen.'"/', $html, $cocok);

        return html_entity_decode($cocok[1]);
    }

    protected function kirimAksi(string $snapshot, string $method, array $params)
    {
        return $this->withHeader('X-Livewire', '1')->postJson(Livewire::getUpdateUri(), [
            'components' => [[
                'snapshot' => $snapshot,
                'updates' => [],
                'calls' => [['path' => '', 'method' => $method, 'params' => $params]],
            ]],
        ]);
    }
}

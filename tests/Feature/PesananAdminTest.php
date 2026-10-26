<?php

namespace Tests\Feature;

use App\Enums\StatusPesanan;
use App\Livewire\Admin\DaftarPesanan;
use App\Livewire\Admin\DetailPesanan;
use App\Models\admin;
use App\Models\Keranjang;
use App\Models\Pesanan;
use App\Models\User;
use App\Models\Varian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class PesananAdminTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    protected Varian $varian;

    protected admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->varian = Varian::factory()->create(['harga' => 45000, 'stok' => 10]);

        $this->admin = admin::create([
            'name' => 'administrator',
            'email' => 'admin@example.com',
            'password' => bcrypt('rahasia123'),
        ]);
        $this->actingAs($this->admin, 'adminMiddle');
    }

    protected function buatPesanan(StatusPesanan ...$jalur): Pesanan
    {
        $user = User::factory()->create();
        $keranjang = Keranjang::create(['user_id' => $user->id]);
        $keranjang->tambah($this->varian, 2);
        $pesanan = $this->pesananDari($keranjang, $this->alamatUntuk($user));

        foreach ($jalur as $status) {
            $status === StatusPesanan::Dikirim ? $pesanan->kirim('JNE0001') : $pesanan->ubahStatus($status);
        }

        return $pesanan->fresh();
    }

    public function test_daftar_pesanan_bisa_difilter_per_status_dengan_jumlahnya()
    {
        $menunggu = $this->buatPesanan();
        $dibayar = $this->buatPesanan(StatusPesanan::Dibayar);
        $dibayarLagi = $this->buatPesanan(StatusPesanan::Dibayar);
        $diproses = $this->buatPesanan(StatusPesanan::Dibayar, StatusPesanan::Diproses);

        $this->get('/admin/pesanan')->assertOk()
            ->assertSee($menunggu->nomor)->assertSee($diproses->nomor)
            ->assertSeeInOrder(['Semua', '4', 'Menunggu pembayaran', '1', 'Dibayar', '2', 'Diproses', '1']);

        Livewire::withQueryParams(['status' => 'dibayar'])->test(DaftarPesanan::class)
            ->assertSee($dibayar->nomor)->assertSee($dibayarLagi->nomor)
            ->assertDontSee($menunggu->nomor)->assertDontSee($diproses->nomor)
            ->set('status', 'diproses')
            ->assertSee($diproses->nomor)->assertDontSee($dibayar->nomor);
    }

    public function test_detail_pesanan_menampilkan_pembeli_alamat_barang_kurir_dan_pembayaran()
    {
        $pesanan = $this->buatPesanan();
        $this->actingAs($pesanan->user)->post(route('pembayaran.simulasi', $pesanan), ['status' => 'settlement']);

        $this->actingAs($this->admin, 'adminMiddle')->get(route('admin.pesanan.detail', $pesanan))->assertOk()
            ->assertSee($pesanan->user->name)->assertSee($pesanan->user->email)
            ->assertSee('Budi Santoso')->assertSee('Jl. Merdeka 1, Bandung')
            ->assertSee($this->varian->produk->nama)
            ->assertSee('JNE Reguler')
            ->assertSee('Dibayar')
            ->assertSee('Rp 108.000'); // 90.000 + JNE 18.000
    }

    public function test_admin_memproses_mengirim_dengan_resi_lalu_menyelesaikan_pesanan()
    {
        $pesanan = $this->buatPesanan(StatusPesanan::Dibayar);

        Livewire::test(DetailPesanan::class, ['pesanan' => $pesanan])
            ->call('proses')->assertHasNoErrors()
            ->assertSee('Diproses')
            ->set('resi', ' jne0012345678 ')
            ->call('kirim')->assertHasNoErrors()
            ->assertSee('Paket dalam perjalanan');

        $pesanan->refresh();
        $this->assertSame(StatusPesanan::Dikirim, $pesanan->status);
        $this->assertSame('JNE0012345678', $pesanan->pengiriman->resi);
        $this->assertNotNull($pesanan->diproses_at);
        $this->assertNotNull($pesanan->dikirim_at);

        Livewire::test(DetailPesanan::class, ['pesanan' => $pesanan])->call('selesaikan')->assertHasNoErrors();

        $this->assertSame(StatusPesanan::Selesai, $pesanan->fresh()->status);
    }

    public function test_dikirim_wajib_resi()
    {
        $pesanan = $this->buatPesanan(StatusPesanan::Dibayar, StatusPesanan::Diproses);

        Livewire::test(DetailPesanan::class, ['pesanan' => $pesanan])
            ->set('resi', '  ')
            ->call('kirim')->assertHasErrors('resi');

        $this->assertSame(StatusPesanan::Diproses, $pesanan->fresh()->status);
        $this->assertNull($pesanan->fresh()->pengiriman->resi);
    }

    public function test_pesanan_yang_belum_diproses_atau_belum_dibayar_tidak_bisa_dikirim()
    {
        foreach ([$this->buatPesanan(), $this->buatPesanan(StatusPesanan::Dibayar)] as $pesanan) {
            $status = $pesanan->status;

            Livewire::test(DetailPesanan::class, ['pesanan' => $pesanan])
                ->set('resi', 'JNE0012345678')
                ->call('kirim')->assertHasErrors('status');

            $this->assertSame($status, $pesanan->fresh()->status);
            $this->assertNull($pesanan->fresh()->pengiriman->resi);
        }
    }

    public function test_admin_membatalkan_pesanan_menunggu_pembayaran_dan_stoknya_kembali()
    {
        $pesanan = $this->buatPesanan();
        $this->assertSame(8, $this->varian->fresh()->stok);

        Livewire::test(DetailPesanan::class, ['pesanan' => $pesanan])->call('batalkan')->assertHasNoErrors();

        $this->assertSame(StatusPesanan::Dibatalkan, $pesanan->fresh()->status);
        $this->assertNotNull($pesanan->fresh()->dibatalkan_at);
        $this->assertSame(10, $this->varian->fresh()->stok);
    }

    public function test_transisi_di_luar_mesin_status_ditolak()
    {
        $dibayar = $this->buatPesanan(StatusPesanan::Dibayar);
        $menunggu = $this->buatPesanan();
        $dikirim = $this->buatPesanan(StatusPesanan::Dibayar, StatusPesanan::Diproses, StatusPesanan::Dikirim);

        // [pesanan, aksi]: pesanan dibayar tidak bisa dibatalkan, pesanan belum dibayar tidak bisa diproses,
        // pesanan yang belum dikirim tidak bisa selesai, dan pesanan dikirim tidak bisa diproses ulang
        foreach ([[$dibayar, 'batalkan'], [$menunggu, 'proses'], [$menunggu, 'selesaikan'], [$dibayar, 'selesaikan'], [$dikirim, 'proses']] as [$pesanan, $aksi]) {
            $status = $pesanan->status;

            Livewire::test(DetailPesanan::class, ['pesanan' => $pesanan])->call($aksi)->assertHasErrors('status');

            $this->assertSame($status, $pesanan->fresh()->status, "$aksi pada pesanan {$status->label()}");
        }
        $this->assertSame(4, $this->varian->fresh()->stok);
    }
}

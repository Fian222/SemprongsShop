<?php

namespace Tests\Feature;

use App\Enums\StatusPesanan;
use App\Livewire\Dashboard;
use App\Models\admin;
use App\Models\Keranjang;
use App\Models\Pesanan;
use App\Models\User;
use App\Models\Varian;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class EksporPesananTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    protected Varian $varian;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->varian = Varian::factory()->create(['nama' => 'Toples 250 g', 'harga' => 45000, 'stok' => 100]);
        $this->varian->produk->update(['nama' => 'Semprong Pandan']);

        $this->actingAs(admin::create([
            'name' => 'administrator',
            'email' => 'admin@example.com',
            'password' => bcrypt('rahasia123'),
        ]), 'adminMiddle');
    }

    // pesanan 2 × Rp 45.000 yang dibuat pada $waktu
    protected function pesananPada(string $waktu, string $nama = 'Budi Santoso', ?string $kodeVoucher = null): Pesanan
    {
        $this->travelTo(Carbon::parse($waktu));
        $user = User::factory()->create(['name' => $nama, 'email' => 'budi'.Pesanan::count().'@example.com']);
        $keranjang = Keranjang::create(['user_id' => $user->id]);
        $keranjang->tambah($this->varian, 2);

        return $this->pesananDari($keranjang, $this->alamatUntuk($user), kodeVoucher: $kodeVoucher);
    }

    // baris CSV tanpa BOM
    protected function bacaCsv(string $isi): array
    {
        $this->assertStringStartsWith("\u{FEFF}", $isi, 'BOM supaya Excel membaca UTF-8');

        return array_map(fn ($baris) => str_getcsv($baris, escape: ''), explode("\r\n", rtrim(substr($isi, 3), "\r\n")));
    }

    public function test_ekspor_csv_berisi_pesanan_di_rentang_tanggal_dibuat()
    {
        Voucher::factory()->nominal(10000)->create(['kode' => 'HEMAT']);

        $this->pesananPada('2026-09-30 23:59');                                    // sebelum rentang
        $awal = $this->pesananPada('2026-10-01 00:00', kodeVoucher: 'HEMAT');
        $awal->ubahStatus(StatusPesanan::Dibayar);
        $batal = $this->pesananPada('2026-10-15 12:00', 'Sari, "Mawar"');
        $batal->ubahStatus(StatusPesanan::Dibatalkan);
        $akhir = $this->pesananPada('2026-10-27 23:59');
        $this->pesananPada('2026-10-28 00:00');                                    // sesudah rentang

        $respons = $this->get(route('admin.pesanan.ekspor', ['dari' => '2026-10-01', 'sampai' => '2026-10-27']))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertDownload('pesanan-2026-10-01-sampai-2026-10-27.csv');

        $baris = $this->bacaCsv($respons->streamedContent());

        $this->assertSame([
            'Nomor', 'Tanggal pesan', 'Status', 'Pelanggan', 'Email', 'Penerima', 'No. HP', 'Area tujuan', 'Barang',
            'Subtotal', 'Diskon', 'Voucher', 'Ongkir', 'Total', 'Kurir', 'Resi', 'Dibayar pada',
        ], $baris[0]);
        $this->assertSame([$awal->nomor, $batal->nomor, $akhir->nomor], array_column(array_slice($baris, 1), 0));

        $this->assertSame([
            $awal->nomor, '2026-10-01 00:00:00', 'Dibayar', 'Budi Santoso', $awal->user->email, 'Budi Santoso', '081234567890',
            'Bandung Wetan, Bandung, Jawa Barat. 40115', 'Semprong Pandan – Toples 250 g × 2',
            '90000', '10000', 'HEMAT', '18000', '98000', 'JNE Reguler', '', '2026-10-01 00:00:00',
        ], $baris[1]);
        $this->assertSame(['Dibatalkan', 'Sari, "Mawar"'], [$baris[2][2], $baris[2][3]]);
        $this->assertSame('', $baris[2][16]);
    }

    public function test_isi_yang_diawali_rumus_tidak_dijalankan_spreadsheet()
    {
        $this->pesananPada('2026-10-10 10:00', '=HYPERLINK("http://jahat.example")');

        $baris = $this->bacaCsv($this->get(route('admin.pesanan.ekspor', ['dari' => '2026-10-10', 'sampai' => '2026-10-10']))->streamedContent());

        $this->assertSame('\'=HYPERLINK("http://jahat.example")', $baris[1][3]);
    }

    public function test_form_di_dashboard_mengunduh_rentang_yang_dipilih()
    {
        $this->travelTo(Carbon::parse('2026-10-27 10:00'));

        Livewire::test(Dashboard::class)
            ->assertSet('dari', '2026-09-28')->assertSet('sampai', '2026-10-27')
            ->set('dari', '2026-10-27')->set('sampai', '2026-10-01')
            ->call('ekspor')
            ->assertHasErrors('sampai')->assertSee('Tanggal akhir tidak boleh sebelum tanggal awal.')
            ->set('sampai', '2026-10-31')
            ->call('ekspor')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.pesanan.ekspor', ['dari' => '2026-10-27', 'sampai' => '2026-10-31']));
    }

    public function test_rentang_tanggal_wajib_dan_tidak_terbalik()
    {
        $this->from('/admin/dashboard');

        $this->get(route('admin.pesanan.ekspor'))->assertRedirect('/admin/dashboard')->assertSessionHasErrors(['dari', 'sampai']);
        $this->get(route('admin.pesanan.ekspor', ['dari' => '2026-10-27', 'sampai' => '2026-10-01']))
            ->assertRedirect('/admin/dashboard')->assertSessionHasErrors('sampai');
    }

    public function test_tamu_tidak_bisa_mengekspor_pesanan()
    {
        $this->app['auth']->guard('adminMiddle')->logout();

        $this->get(route('admin.pesanan.ekspor', ['dari' => '2026-10-01', 'sampai' => '2026-10-27']))->assertRedirect(url('admin/login'));
    }
}

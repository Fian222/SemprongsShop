<?php

namespace Tests\Feature;

use App\Enums\StatusPesanan;
use App\Models\admin;
use App\Models\Keranjang;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\User;
use App\Models\Varian;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class DashboardAdminTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    protected Varian $varian;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->travelTo(Carbon::parse('2026-10-27 10:00'));
        $this->varian = Varian::factory()->create(['harga' => 45000, 'stok' => 100]);

        $this->actingAs(admin::create([
            'name' => 'administrator',
            'email' => 'admin@example.com',
            'password' => bcrypt('rahasia123'),
        ]), 'adminMiddle');
    }

    // pesanan $jumlah × Rp 45.000 yang dibuat sekarang lalu menempuh $jalur status
    protected function buatPesanan(int $jumlah = 2, array $jalur = [], ?string $kodeVoucher = null, ?Varian $varian = null): Pesanan
    {
        $user = User::factory()->create();
        $keranjang = Keranjang::create(['user_id' => $user->id]);
        $keranjang->tambah($varian ?? $this->varian, $jumlah);
        $pesanan = $this->pesananDari($keranjang, $this->alamatUntuk($user), kodeVoucher: $kodeVoucher);

        foreach ($jalur as $status) {
            $status === StatusPesanan::Dikirim ? $pesanan->kirim('JNE0001') : $pesanan->ubahStatus($status);
        }

        return $pesanan->fresh();
    }

    // pesanan yang dibayar $hariLalu hari sebelum hari ini
    protected function dibayar(int $hariLalu, int $jumlah = 2, ?string $kodeVoucher = null, ?Varian $varian = null): Pesanan
    {
        $sekarang = now();
        $this->travelTo($sekarang->copy()->subDays($hariLalu));
        $pesanan = $this->buatPesanan($jumlah, [StatusPesanan::Dibayar], $kodeVoucher, $varian);
        $this->travelTo($sekarang);

        return $pesanan;
    }

    public function test_omzet_hanya_dari_pesanan_dibayar_dikurangi_diskon_tanpa_ongkir()
    {
        Voucher::factory()->nominal(10000)->create(['kode' => 'HEMAT']);

        $this->dibayar(0);                         // 90.000
        $this->dibayar(0, 1, kodeVoucher: 'HEMAT'); // 45.000 − 10.000
        $this->dibayar(5, 4);                      // 180.000, masih dalam 30 hari
        $this->dibayar(40, 10);                    // di luar 30 hari

        // belum dibayar, kedaluwarsa, dan dibatalkan tidak dihitung
        $this->buatPesanan(3);
        $this->buatPesanan(3, [StatusPesanan::Kedaluwarsa]);
        $this->buatPesanan(3, [StatusPesanan::Dibatalkan]);

        $this->get('/admin/dashboard')->assertOk()
            ->assertSeeInOrder(['Omzet hari ini', 'Rp 125.000', 'Omzet 30 hari terakhir', 'Rp 305.000']);
    }

    public function test_omzet_masuk_pada_hari_dibayar_dan_tetap_terhitung_sampai_selesai()
    {
        // dibuat kemarin malam, dibayar hari ini dini hari
        $this->travelTo(Carbon::parse('2026-10-26 23:00'));
        $pesanan = $this->buatPesanan(1);
        $this->travelTo(Carbon::parse('2026-10-27 01:00'));
        $pesanan->ubahStatus(StatusPesanan::Dibayar);

        // dibayar hari ini lalu sampai ke pembeli
        $this->buatPesanan(2, [StatusPesanan::Dibayar, StatusPesanan::Diproses, StatusPesanan::Dikirim, StatusPesanan::Selesai]);
        $this->travelTo(Carbon::parse('2026-10-27 10:00'));

        $this->get('/admin/dashboard')->assertOk()
            ->assertSeeInOrder(['Omzet hari ini', 'Rp 135.000'])
            ->assertSeeInOrder(['26 Okt 2026', 'Rp 0', '27 Okt 2026', 'Rp 135.000']);
    }

    public function test_jumlah_pesanan_per_status_menautkan_ke_daftar_pesanan()
    {
        $this->buatPesanan();
        $this->buatPesanan(jalur: [StatusPesanan::Dibayar]);
        $this->buatPesanan(jalur: [StatusPesanan::Dibayar]);
        $this->buatPesanan(jalur: [StatusPesanan::Dibayar, StatusPesanan::Diproses, StatusPesanan::Dikirim]);
        $this->buatPesanan(jalur: [StatusPesanan::Dibatalkan]);

        $this->get('/admin/dashboard')->assertOk()
            ->assertSeeInOrder([
                'Menunggu pembayaran', '1', 'Dibayar', '2', 'Diproses', '0', 'Dikirim', '1',
                'Selesai', '0', 'Kedaluwarsa', '0', 'Dibatalkan', '1',
            ])
            ->assertSee(route('admin.pesanan', ['status' => 'dibayar']), false);
    }

    public function test_stok_menipis_menautkan_ke_halaman_ubah_varian()
    {
        $menipis = Varian::factory()->create(['nama' => 'Toples Menipis', 'stok' => 3]);
        Varian::factory()->create(['nama' => 'Toples Habis', 'stok' => 0]);
        Varian::factory()->create(['nama' => 'Toples Cukup', 'stok' => 10]);
        Varian::factory()->create(['nama' => 'Toples Nonaktif', 'stok' => 2, 'aktif' => false]);

        $tautan = route('admin.produk.ubah', ['produk' => $menipis->produk_id, 'varian' => $menipis->id]);

        $this->get('/admin/dashboard')->assertOk()
            ->assertSee('Toples Menipis')->assertSee($menipis->produk->nama)
            ->assertSee($tautan, false)
            ->assertDontSee('Toples Habis')->assertDontSee('Toples Cukup')->assertDontSee('Toples Nonaktif');

        $this->get($tautan)->assertOk()->assertSee('Ubah varian Toples Menipis');
    }

    public function test_produk_terlaris_30_hari_dari_pesanan_yang_sudah_dibayar()
    {
        $varian = fn (string $produk) => Varian::factory()->for(Produk::factory()->create(['nama' => $produk]))->create(['stok' => 100]);
        $pandan = $varian('Semprong Pandan');
        $wijen = $varian('Semprong Wijen');
        $keju = $varian('Semprong Keju');
        $coklat = $varian('Semprong Coklat');

        $this->dibayar(1, 2, varian: $wijen);
        $this->dibayar(3, 4, varian: $wijen);   // wijen 6
        $this->dibayar(0, 5, varian: $pandan);  // pandan 5
        $this->buatPesanan(9, [StatusPesanan::Dibayar, StatusPesanan::Diproses], varian: $this->varian); // tetap dihitung
        $this->buatPesanan(20, varian: $keju);  // belum dibayar
        $this->buatPesanan(20, [StatusPesanan::Dibatalkan], varian: $keju);
        $this->dibayar(45, 30, varian: $coklat); // di luar 30 hari

        $this->get('/admin/dashboard')->assertOk()
            ->assertSeeInOrder(['Terlaris', $this->varian->produk->nama, '9 terjual', 'Semprong Wijen', '6 terjual', 'Semprong Pandan', '5 terjual'])
            ->assertDontSee('Semprong Keju')->assertDontSee('Semprong Coklat');
    }

    public function test_omzet_harian_30_hari_termasuk_hari_tanpa_penjualan()
    {
        $this->dibayar(0);      // 27 Okt: 90.000
        $this->dibayar(0, 1);   // 27 Okt: + 45.000
        $this->dibayar(5, 4);   // 22 Okt: 180.000
        $this->dibayar(29, 1);  // 28 Sep: 45.000, hari pertama grafik
        $this->dibayar(30, 10); // 27 Sep: di luar grafik
        $this->buatPesanan(3, [StatusPesanan::Dibatalkan]);

        $this->get('/admin/dashboard')->assertOk()
            ->assertSee('Omzet harian')
            ->assertSeeInOrder([
                '28 Sep 2026', 'Rp 45.000',
                '29 Sep 2026', 'Rp 0',
                '22 Okt 2026', 'Rp 180.000',
                '23 Okt 2026', 'Rp 0',
                '27 Okt 2026', 'Rp 135.000',
            ])
            ->assertDontSee('27 Sep 2026');
    }
}

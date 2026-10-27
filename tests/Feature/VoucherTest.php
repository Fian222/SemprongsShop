<?php

namespace Tests\Feature;

use App\Enums\StatusPesanan;
use App\Livewire\Checkout;
use App\Models\Keranjang;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\User;
use App\Models\Varian;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

/**
 * Aturan voucher dilihat dari checkout pembeli: diskon yang tampil, alasan penolakan, dan pesanan yang tercatat.
 * Ongkir simulasi JNE REG untuk 600 g adalah Rp 18.000.
 */
class VoucherTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    protected Varian $varian;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->travelTo(now()->setDate(2026, 10, 27)->setTime(10, 0));
        $this->varian = Varian::factory()
            ->for(Produk::factory()->state(['nama' => 'Semprong Pandan']))
            ->create(['nama' => 'Toples kecil', 'harga' => 45000, 'stok' => 10, 'berat' => 300]);
    }

    // pelanggan yang sedang membuka checkout dengan $jumlah toples di tasnya (subtotal 45.000 × $jumlah)
    protected function checkout(int $jumlah = 2)
    {
        $user = User::factory()->create();
        $this->alamatUntuk($user);
        $this->actingAs($user);
        Keranjang::milikPengunjungAtauBaru()->tambah($this->varian, $jumlah);

        return Livewire::test(Checkout::class);
    }

    public function test_diskon_persen_tampil_di_checkout_dan_tercatat_di_pesanan()
    {
        Voucher::factory()->persen(10)->create(['kode' => 'HEMAT10']);

        $this->checkout()
            ->set('kodeVoucher', ' hemat10 ')
            ->call('pakaiVoucher')
            ->assertHasNoErrors()
            ->assertSee('HEMAT10')
            ->assertSee('−Rp 9.000')
            ->set('kurir', 'jne:reg')
            ->assertSee('Rp 99.000')
            ->call('bayar')
            ->assertHasNoErrors();

        $pesanan = Pesanan::firstOrFail();
        $this->assertSame([90000, 18000, 9000, 99000], [$pesanan->subtotal, $pesanan->ongkir, $pesanan->diskon, $pesanan->total]);
        $this->assertSame('HEMAT10', $pesanan->voucher_kode);
        $this->get(route('pesanan.detail', $pesanan))->assertSeeInOrder(['Diskon HEMAT10', '−Rp 9.000']);
    }

    public function test_diskon_persen_dibatasi_maksimal_diskon()
    {
        Voucher::factory()->persen(20, maksDiskon: 15000)->create(['kode' => 'LEBARAN']);

        $this->checkout(4)
            ->set('kodeVoucher', 'LEBARAN')
            ->call('pakaiVoucher')
            ->assertSee('−Rp 15.000')
            ->set('kurir', 'jne:reg')
            ->call('bayar');

        // 20% dari 180.000 adalah 36.000, tetapi maksimalnya 15.000
        $this->assertSame(15000, Pesanan::firstOrFail()->diskon);
    }

    public function test_diskon_nominal_tidak_pernah_melebihi_subtotal()
    {
        Voucher::factory()->nominal(100000)->create(['kode' => 'GRATIS']);

        $this->checkout(1)
            ->set('kodeVoucher', 'GRATIS')
            ->call('pakaiVoucher')
            ->set('kurir', 'sicepat:reg')
            ->call('bayar')
            ->assertHasNoErrors();

        $pesanan = Pesanan::firstOrFail();
        $this->assertSame(45000, $pesanan->diskon);
        $this->assertSame($pesanan->ongkir, $pesanan->total);
    }

    public static function voucherTidakBerlaku(): array
    {
        return [
            'kedaluwarsa' => [['berakhir' => '2026-10-26 23:59'], 'Voucher PROMO sudah berakhir pada 26 Okt 2026, 23:59.'],
            'belum mulai' => [['mulai' => '2026-11-01 00:00'], 'Voucher PROMO baru berlaku mulai 1 Nov 2026, 00:00.'],
            'nonaktif' => [['aktif' => false], 'Voucher PROMO sedang tidak berlaku.'],
            'kuota habis' => [['kuota' => 0], 'Kuota voucher PROMO sudah habis.'],
            'di bawah minimum' => [['min_subtotal' => 100000], 'Voucher PROMO berlaku untuk belanja minimal Rp 100.000. Tambah Rp 10.000 lagi untuk memakainya.'],
        ];
    }

    #[DataProvider('voucherTidakBerlaku')]
    public function test_voucher_yang_tidak_berlaku_ditolak_dengan_alasannya(array $data, string $pesan)
    {
        Voucher::factory()->create(array_merge(['kode' => 'PROMO'], $data));

        $this->checkout()
            ->set('kodeVoucher', 'promo')
            ->call('pakaiVoucher')
            ->assertHasErrors('kodeVoucher')
            ->assertSee($pesan)
            ->assertSet('voucherDiterima', null);
    }

    public function test_kode_yang_tidak_dikenal_ditolak()
    {
        $this->checkout()
            ->set('kodeVoucher', 'ngarang')
            ->call('pakaiVoucher')
            ->assertSee('Kode voucher NGARANG tidak dikenal.')
            ->assertSet('voucherDiterima', null);
    }

    public function test_voucher_yang_diterima_tidak_bisa_diganti_dari_browser()
    {
        $halaman = $this->checkout();
        $this->expectException(CannotUpdateLockedPropertyException::class);

        $halaman->set('voucherDiterima', 'NGARANG');
    }

    // pesanan pelanggan lain yang memakai $kode dan masih menunggu pembayaran
    protected function pesananLainDengan(string $kode): Pesanan
    {
        $user = User::factory()->create();
        $keranjang = Keranjang::create(['user_id' => $user->id]);
        $keranjang->tambah($this->varian, 2);

        return $this->pesananDari($keranjang, $this->alamatUntuk($user), kodeVoucher: $kode);
    }

    public static function pesananBerakhirTanpaDibayar(): array
    {
        return [[StatusPesanan::Kedaluwarsa], [StatusPesanan::Dibatalkan]];
    }

    #[DataProvider('pesananBerakhirTanpaDibayar')]
    public function test_kuota_yang_dipegang_pesanan_tak_dibayar_dilepas_lagi(StatusPesanan $akhir)
    {
        Voucher::factory()->create(['kode' => 'SATU', 'kuota' => 1]);
        $pesananLain = $this->pesananLainDengan('SATU');

        $halaman = $this->checkout()
            ->set('kodeVoucher', 'SATU')
            ->call('pakaiVoucher')
            ->assertSee('Kuota voucher SATU sudah habis.');

        $pesananLain->ubahStatus($akhir);

        $halaman->call('pakaiVoucher')
            ->assertHasNoErrors()
            ->assertSet('voucherDiterima', 'SATU');
    }

    public function test_kuota_yang_habis_sesudah_voucher_diterima_menahan_pesanan()
    {
        Voucher::factory()->nominal(10000)->create(['kode' => 'SATU', 'kuota' => 1]);
        $halaman = $this->checkout()
            ->set('kodeVoucher', 'SATU')
            ->call('pakaiVoucher')
            ->set('kurir', 'jne:reg');

        $this->pesananLainDengan('SATU');

        $halaman->call('bayar')
            ->assertHasErrors('kodeVoucher')
            ->assertSee('Kuota voucher SATU sudah habis. Vouchernya dilepas.')
            ->assertSet('voucherDiterima', null)
            ->assertSee('Rp 108.000');

        $this->assertSame(1, Pesanan::count());
    }
}

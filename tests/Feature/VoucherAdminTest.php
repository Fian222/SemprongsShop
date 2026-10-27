<?php

namespace Tests\Feature;

use App\Enums\StatusPesanan;
use App\Enums\TipeVoucher;
use App\Livewire\Admin\KelolaVoucher;
use App\Models\admin;
use App\Models\Keranjang;
use App\Models\Pesanan;
use App\Models\User;
use App\Models\Varian;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class VoucherAdminTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

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

    protected function pesananDengan(string $kode, StatusPesanan $status = StatusPesanan::MenungguPembayaran): Pesanan
    {
        $user = User::factory()->create();
        $keranjang = Keranjang::create(['user_id' => $user->id]);
        $keranjang->tambah(Varian::factory()->create(), 1);
        $pesanan = $this->pesananDari($keranjang, $this->alamatUntuk($user), kodeVoucher: $kode);

        if ($status !== StatusPesanan::MenungguPembayaran) {
            $pesanan->ubahStatus($status);
        }

        return $pesanan;
    }

    public function test_halaman_voucher_hanya_untuk_admin()
    {
        $this->get('/admin/voucher')->assertOk()->assertSee('Voucher');

        auth('adminMiddle')->logout();
        $this->get('/admin/voucher')->assertRedirect();
    }

    public function test_admin_membuat_dan_mengubah_voucher()
    {
        Livewire::test(KelolaVoucher::class)
            ->set('form.kode', ' hemat10 ')
            ->set('form.tipe', 'persen')
            ->set('form.nilai', 10)
            ->set('form.maks_diskon', 15000)
            ->set('form.min_subtotal', 50000)
            ->set('form.kuota', 100)
            ->set('form.mulai', '2026-11-01T00:00')
            ->set('form.berakhir', '2026-11-30T23:59')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSee('HEMAT10');

        $voucher = Voucher::firstOrFail();
        $this->assertSame(
            ['HEMAT10', TipeVoucher::Persen, 10, 15000, 50000, 100, '2026-11-01 00:00', '2026-11-30 23:59', true],
            [$voucher->kode, $voucher->tipe, $voucher->nilai, $voucher->maks_diskon, $voucher->min_subtotal, $voucher->kuota,
                $voucher->mulai->format('Y-m-d H:i'), $voucher->berakhir->format('Y-m-d H:i'), $voucher->aktif],
        );

        Livewire::test(KelolaVoucher::class)
            ->call('ubah', $voucher->id)
            ->assertSet('form.kode', 'HEMAT10')
            ->assertSet('form.mulai', '2026-11-01T00:00')
            ->set('form.tipe', 'nominal')
            ->set('form.nilai', 20000)
            ->set('form.maks_diskon', '')
            ->set('form.kuota', '')
            ->set('form.berakhir', '')
            ->set('form.aktif', false)
            ->call('simpan')
            ->assertHasNoErrors();

        $voucher->refresh();
        $this->assertSame([TipeVoucher::Nominal, 20000, null, null, null, false],
            [$voucher->tipe, $voucher->nilai, $voucher->maks_diskon, $voucher->kuota, $voucher->berakhir, $voucher->aktif]);
    }

    public function test_voucher_yang_tidak_masuk_akal_ditolak()
    {
        Voucher::factory()->create(['kode' => 'HEMAT10']);

        Livewire::test(KelolaVoucher::class)
            ->set('form.kode', 'hemat10')
            ->set('form.tipe', 'persen')
            ->set('form.nilai', 120)
            ->set('form.mulai', '2026-11-30T00:00')
            ->set('form.berakhir', '2026-11-01T00:00')
            ->call('simpan')
            ->assertHasErrors(['form.kode', 'form.nilai', 'form.berakhir']);

        Livewire::test(KelolaVoucher::class)
            ->set('form.kode', 'ada spasi')
            ->set('form.nilai', 0)
            ->call('simpan')
            ->assertHasErrors(['form.kode', 'form.nilai']);

        $this->assertSame(1, Voucher::count());
    }

    public function test_pemakaian_hanya_menghitung_pesanan_yang_dibayar()
    {
        Voucher::factory()->create(['kode' => 'HEMAT10', 'kuota' => 50]);
        $this->pesananDengan('HEMAT10', StatusPesanan::Dibayar);
        $this->pesananDengan('HEMAT10', StatusPesanan::Dibayar)->ubahStatus(StatusPesanan::Diproses);
        $this->pesananDengan('HEMAT10');
        $this->pesananDengan('HEMAT10', StatusPesanan::Kedaluwarsa);
        $this->pesananDengan('HEMAT10', StatusPesanan::Dibatalkan);

        Livewire::test(KelolaVoucher::class)
            ->assertSeeInOrder(['HEMAT10', '2 / 50', '1 menunggu pembayaran']);
    }

    public function test_voucher_yang_belum_pernah_dipakai_bisa_dihapus()
    {
        $baru = Voucher::factory()->create(['kode' => 'BARU']);
        $dipakai = Voucher::factory()->create(['kode' => 'LAMA']);
        $pesanan = $this->pesananDengan('LAMA', StatusPesanan::Kedaluwarsa);

        Livewire::test(KelolaVoucher::class)
            ->call('hapus', $baru->id)
            ->assertSee('Voucher BARU dihapus.')
            ->call('hapus', $dipakai->id)
            ->assertSee('Voucher LAMA sudah dipakai pesanan. Nonaktifkan saja supaya tidak bisa dipakai lagi.');

        $this->assertModelMissing($baru);
        $this->assertModelExists($dipakai);
        $this->assertSame('LAMA', $pesanan->fresh()->voucher_kode);
    }
}

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
use App\Support\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Constraints\SeeInOrder;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    protected Pesanan $pesanan;

    protected function setUp(): void
    {
        parent::setUp();

        $varian = Varian::factory()
            ->for(Produk::factory()->state(['nama' => 'Semprong Pandan']))
            ->create(['nama' => 'Toples besar', 'sku' => 'PDN-TB', 'harga' => 45000, 'berat' => 500, 'stok' => 10]);
        Voucher::factory()->persen(10)->create(['kode' => 'HEMAT10']);

        $user = User::factory()->create();
        $keranjang = Keranjang::create(['user_id' => $user->id]);
        $keranjang->tambah($varian, 2);
        $this->pesanan = $this->pesananDari($keranjang, $this->alamatUntuk($user), 'jne:reg', 'HEMAT10');
    }

    public function test_invoice_berisi_nomor_item_pesanan_rincian_harga_dan_status_pembayaran()
    {
        // 2 × Rp 45.000; ongkir JNE Reguler 1 kg Rp 18.000; diskon 10% Rp 9.000
        $this->assertSeeInOrder((new Invoice($this->pesanan))->html(), [
            $this->pesanan->nomor,
            'Semprong Pandan', 'Toples besar', 'Rp 45.000', '2', 'Rp 90.000',
            'Belum dibayar',
            'Subtotal', 'Rp 90.000', 'Ongkir JNE', 'Rp 18.000', 'Diskon HEMAT10', '−Rp 9.000', 'Total', 'Rp 99.000',
        ]);

        $this->pesanan->ubahStatus(StatusPesanan::Dibayar);

        $html = (new Invoice($this->pesanan->fresh()))->html();
        $this->assertStringContainsString('Dibayar', $html);
        $this->assertStringNotContainsString('Belum dibayar', $html);
    }

    protected function assertSeeInOrder(string $html, array $urutan): void
    {
        $this->assertThat(array_map('e', $urutan), new SeeInOrder($html));
    }

    public function test_pemilik_pesanan_bisa_mengunduh_invoice_pdf()
    {
        $this->actingAs($this->pesanan->user)->get(route('pesanan.invoice', $this->pesanan))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertDownload("invoice-{$this->pesanan->nomor}.pdf");
    }

    public function test_admin_bisa_mengunduh_invoice_pdf()
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'adminMiddle')->get(route('admin.pesanan.invoice', $this->pesanan))
            ->assertOk()
            ->assertDownload("invoice-{$this->pesanan->nomor}.pdf");
    }

    public function test_halaman_pesanan_pelanggan_dan_admin_menautkan_invoice()
    {
        $admin = $this->admin();
        $this->withoutVite();

        $this->actingAs($this->pesanan->user)->get(route('pesanan.detail', $this->pesanan))
            ->assertSee(route('pesanan.invoice', $this->pesanan));
        $this->actingAs($admin, 'adminMiddle')->get(route('admin.pesanan.detail', $this->pesanan))
            ->assertSee(route('admin.pesanan.invoice', $this->pesanan));
    }

    public function test_pelanggan_lain_mendapat_403()
    {
        $this->actingAs(User::factory()->create())->get(route('pesanan.invoice', $this->pesanan))->assertForbidden();
    }

    public function test_pelanggan_tidak_bisa_memakai_rute_invoice_admin()
    {
        $this->actingAs($this->pesanan->user)->get(route('admin.pesanan.invoice', $this->pesanan))
            ->assertRedirect(url('admin/login'));
    }

    public function test_invoice_pesanan_yang_dibayar_sesudah_kedaluwarsa_tidak_tampil_dibayar()
    {
        $this->pesanan->ubahStatus(StatusPesanan::Kedaluwarsa);
        $this->actingAs($this->pesanan->user)->post(route('pembayaran.simulasi', $this->pesanan), ['status' => 'settlement']);

        $html = (new Invoice($this->pesanan->fresh()))->html();
        $this->assertStringContainsString('Dibayar terlambat', $html);
        $this->assertStringContainsString('Pesanan kedaluwarsa sebelum pembayaran masuk', $html);
    }

    public function test_invoice_pesanan_yang_lewat_batas_bayar_tidak_meminta_dibayar()
    {
        $this->travel(25)->hours();

        $html = (new Invoice($this->pesanan))->html();
        $this->assertStringContainsString('Lewat batas bayar', $html);
        $this->assertStringNotContainsString('Bayar sebelum', $html);
    }

    protected function admin(): admin
    {
        return admin::create(['name' => 'administrator', 'email' => 'admin@example.com', 'password' => bcrypt('rahasia123')]);
    }

    public function test_tamu_diarahkan_ke_halaman_masuk()
    {
        $this->get(route('pesanan.invoice', $this->pesanan))->assertRedirect(route('login'));
        $this->get(route('admin.pesanan.invoice', $this->pesanan))->assertRedirect(url('admin/login'));
    }
}

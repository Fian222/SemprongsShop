<?php

namespace Tests\Feature;

use App\Enums\StatusPembayaran;
use App\Enums\StatusPesanan;
use App\Livewire\DetailPesanan;
use App\Models\Keranjang;
use App\Models\Pesanan;
use App\Models\User;
use App\Models\Varian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Webhook diuji lewat HTTP dengan payload dan signature berformat Midtrans, memakai adapter Midtrans sungguhan.
 */
class NotifikasiPembayaranTest extends TestCase
{
    use RefreshDatabase;

    protected const SERVER_KEY = 'SB-Mid-server-UJICOBA';

    protected Varian $varian;

    protected function setUp(): void
    {
        parent::setUp();

        config(['pembayaran.driver' => 'midtrans', 'pembayaran.midtrans.server_key' => self::SERVER_KEY]);
        $this->varian = Varian::factory()->create(['harga' => 45000, 'stok' => 5]);
    }

    protected function buatPesanan(int $jumlah = 2): Pesanan
    {
        $user = User::factory()->create();
        $alamat = $user->alamats()->create([
            'label' => 'Rumah', 'penerima' => 'Budi', 'no_hp' => '081234567890',
            'alamat' => 'Jl. Merdeka 1', 'kode_pos' => '40111', 'utama' => true,
        ]);
        $keranjang = Keranjang::create(['user_id' => $user->id]);
        $keranjang->tambah($this->varian, $jumlah);

        return Pesanan::buatDariKeranjang($keranjang, $alamat);
    }

    // payload seperti contoh notifikasi HTTP(S) di dokumentasi Midtrans
    protected function payload(Pesanan $pesanan, string $status, ?string $grossAmount = null, ?string $serverKey = null): array
    {
        $kode = ['settlement' => '200', 'capture' => '200', 'pending' => '201'][$status] ?? '202';
        $grossAmount ??= $pesanan->total.'.00';

        return [
            'transaction_time' => '2026-10-26 10:05:00',
            'transaction_status' => $status,
            'transaction_id' => '513f1f01-c9da-474c-9fc9-d5c64364b709',
            'status_message' => 'midtrans payment notification',
            'status_code' => $kode,
            'signature_key' => hash('sha512', $pesanan->nomor.$kode.$grossAmount.($serverKey ?? self::SERVER_KEY)),
            'settlement_time' => '2026-10-26 10:06:00',
            'payment_type' => 'qris',
            'order_id' => $pesanan->nomor,
            'merchant_id' => 'G141532850',
            'gross_amount' => $grossAmount,
            'fraud_status' => 'accept',
            'currency' => 'IDR',
        ];
    }

    protected function kirim(array $payload)
    {
        return $this->postJson('/pembayaran/notifikasi', $payload);
    }

    public function test_settlement_mengubah_pesanan_menjadi_dibayar()
    {
        $pesanan = $this->buatPesanan();

        $this->kirim($this->payload($pesanan, 'settlement'))->assertOk();

        $pesanan->refresh();
        $this->assertSame(StatusPesanan::Dibayar, $pesanan->status);
        $this->assertNotNull($pesanan->dibayar_at);
        $this->assertSame(['qris', StatusPembayaran::Dibayar, '513f1f01-c9da-474c-9fc9-d5c64364b709'],
            [$pesanan->pembayaran->metode, $pesanan->pembayaran->status, $pesanan->pembayaran->transaksi_id]);
        $this->assertSame(3, $this->varian->fresh()->stok);
    }

    public function test_signature_salah_ditolak()
    {
        $pesanan = $this->buatPesanan();

        $this->kirim($this->payload($pesanan, 'settlement', serverKey: 'kunci-palsu'))->assertForbidden();
        $payloadTanpaSignature = $this->payload($pesanan, 'settlement');
        unset($payloadTanpaSignature['signature_key']);
        $this->kirim($payloadTanpaSignature)->assertForbidden();

        $this->assertSame(StatusPesanan::MenungguPembayaran, $pesanan->fresh()->status);
    }

    public function test_nominal_tidak_cocok_tidak_membuat_pesanan_dibayar()
    {
        $pesanan = $this->buatPesanan();

        $this->kirim($this->payload($pesanan, 'settlement', '1000.00'))->assertOk();

        $this->assertSame(StatusPesanan::MenungguPembayaran, $pesanan->fresh()->status);
        $this->assertSame(StatusPembayaran::NominalTidakCocok, $pesanan->pembayaran->status);
    }

    public function test_notifikasi_ganda_hanya_diproses_sekali()
    {
        $pesanan = $this->buatPesanan();
        $this->travelTo(now()->setTime(10, 6));
        $this->kirim($this->payload($pesanan, 'settlement'))->assertOk();

        $this->travelTo(now()->setTime(10, 30));
        $this->kirim($this->payload($pesanan, 'settlement'))->assertOk();

        $pesanan->refresh();
        $this->assertSame(StatusPesanan::Dibayar, $pesanan->status);
        $this->assertSame('10:06', $pesanan->dibayar_at->format('H:i'));
        $this->assertSame('10:06', $pesanan->pembayaran->dibayar_at->format('H:i'));
    }

    public function test_expire_dan_cancel_mengembalikan_stok_sekali_saja()
    {
        $kedaluwarsa = $this->buatPesanan(2);
        $batal = $this->buatPesanan(1);
        $this->assertSame(2, $this->varian->fresh()->stok);

        $this->kirim($this->payload($kedaluwarsa, 'expire'))->assertOk();
        $this->kirim($this->payload($kedaluwarsa, 'expire'))->assertOk();
        $this->kirim($this->payload($batal, 'deny'))->assertOk();

        $this->assertSame(StatusPesanan::Kedaluwarsa, $kedaluwarsa->fresh()->status);
        $this->assertSame(StatusPesanan::Dibatalkan, $batal->fresh()->status);
        $this->assertSame(5, $this->varian->fresh()->stok);
    }

    public function test_pembayaran_sesudah_kedaluwarsa_tidak_menghidupkan_pesanan()
    {
        $pesanan = $this->buatPesanan();
        $this->kirim($this->payload($pesanan, 'expire'))->assertOk();

        $this->kirim($this->payload($pesanan, 'settlement'))->assertOk();

        $this->assertSame(StatusPesanan::Kedaluwarsa, $pesanan->fresh()->status);
        $this->assertSame(5, $this->varian->fresh()->stok);
    }

    public function test_pending_tidak_mengubah_status()
    {
        $pesanan = $this->buatPesanan();

        $this->kirim($this->payload($pesanan, 'pending'))->assertOk();

        $this->assertSame(StatusPesanan::MenungguPembayaran, $pesanan->fresh()->status);
    }

    public function test_nomor_pesanan_yang_tidak_dikenal_mendapat_404()
    {
        $pesanan = $this->buatPesanan();
        $payload = $this->payload($pesanan, 'settlement');
        $pesanan->forceFill(['nomor' => 'SMP-LAIN'])->save();

        $this->kirim($payload)->assertNotFound();
    }

    public function test_penjadwal_mengubah_pesanan_lewat_batas_menjadi_kedaluwarsa()
    {
        $lewat = $this->buatPesanan(2);
        $this->travel(23)->hours();
        $masihBerlaku = $this->buatPesanan(1);
        $this->travel(2)->hours();

        $this->artisan('pesanan:kedaluwarsakan')->assertSuccessful();

        $this->assertSame(StatusPesanan::Kedaluwarsa, $lewat->fresh()->status);
        $this->assertSame(StatusPesanan::MenungguPembayaran, $masihBerlaku->fresh()->status);
        $this->assertSame(4, $this->varian->fresh()->stok);
    }

    public function test_server_key_kosong_menolak_semua_notifikasi()
    {
        config(['pembayaran.midtrans.server_key' => '']);
        $pesanan = $this->buatPesanan();

        $this->kirim($this->payload($pesanan, 'settlement', serverKey: ''))->assertForbidden();

        $this->assertSame(StatusPesanan::MenungguPembayaran, $pesanan->fresh()->status);
    }

    public function test_notifikasi_pending_sesudah_settlement_tidak_menurunkan_status_pembayaran()
    {
        $pesanan = $this->buatPesanan();

        $this->kirim($this->payload($pesanan, 'settlement'))->assertOk();
        $this->kirim($this->payload($pesanan, 'pending'))->assertOk();

        $this->assertSame(StatusPembayaran::Dibayar, $pesanan->fresh()->pembayaran->status);
    }

    public function test_pembayaran_sesudah_kedaluwarsa_ditandai_untuk_ditangani_admin()
    {
        $pesanan = $this->buatPesanan();
        $this->kirim($this->payload($pesanan, 'expire'))->assertOk();

        $this->kirim($this->payload($pesanan, 'settlement'))->assertOk();

        $this->assertSame(StatusPembayaran::DibayarTerlambat, $pesanan->fresh()->pembayaran->status);
    }

    public function test_percobaan_pembayaran_yang_gagal_diulang_dengan_order_id_baru()
    {
        Http::fake(['app.sandbox.midtrans.com/*' => Http::sequence()
            ->push('Gateway Timeout', 504)
            ->push(['token' => 'token-kedua', 'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/token-kedua'], 201)]);
        $pesanan = $this->buatPesanan();
        $this->actingAs($pesanan->user);

        Livewire::test(DetailPesanan::class, ['pesanan' => $pesanan])->call('bayar')->assertHasErrors('bayar');
        Livewire::test(DetailPesanan::class, ['pesanan' => $pesanan])->call('bayar')->assertDispatched('buka-pembayaran', token: 'token-kedua');

        $orderIds = collect(Http::recorded())->map(fn ($r) => $r[0]->data()['transaction_details']['order_id']);
        $this->assertSame($pesanan->nomor, $orderIds[0]);
        $this->assertNotSame($orderIds[0], $orderIds[1]);
        $this->assertStringStartsWith($pesanan->nomor.'-', $orderIds[1]);

        // notifikasi untuk order_id percobaan kedua tetap mengenai pesanan ini
        $payload = $this->payload($pesanan, 'settlement');
        $payload['order_id'] = $orderIds[1];
        $payload['signature_key'] = hash('sha512', $orderIds[1].'200'.$payload['gross_amount'].self::SERVER_KEY);
        $this->kirim($payload)->assertOk();

        $this->assertSame(StatusPesanan::Dibayar, $pesanan->fresh()->status);
    }
}

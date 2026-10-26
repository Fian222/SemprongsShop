<?php

namespace Tests\Feature;

use App\Models\Keranjang;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\User;
use App\Models\Varian;
use App\Pembayaran\KejadianPembayaran;
use App\Pembayaran\MidtransGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

// Test kontrak adapter Midtrans Snap: bentuk request dan pembacaan respons/notifikasi, tanpa jaringan
class MidtransGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected function buatPesanan(): Pesanan
    {
        $this->travelTo(now()->setDate(2026, 10, 26)->setTime(10, 0));
        $user = User::factory()->create(['name' => 'Budi', 'email' => 'budi@example.com']);
        $alamat = $user->alamats()->create([
            'label' => 'Rumah', 'penerima' => 'Budi Santoso', 'no_hp' => '081234567890',
            'alamat' => 'Jl. Merdeka 1, Bandung', 'kode_pos' => '40111', 'utama' => true,
        ]);
        $varian = Varian::factory()
            ->for(Produk::factory()->state(['nama' => 'Semprong Pandan']))
            ->create(['nama' => 'Toples kecil', 'sku' => 'SMP-PDN-250', 'harga' => 45000, 'stok' => 5]);
        $keranjang = Keranjang::create(['user_id' => $user->id]);
        $keranjang->tambah($varian, 2);

        return Pesanan::buatDariKeranjang($keranjang, $alamat);
    }

    public function test_membuat_transaksi_snap_dengan_bentuk_request_midtrans()
    {
        Http::fake(['app.sandbox.midtrans.com/*' => Http::response([
            'token' => '66e4fa55-fdac-4ef9-91b5-733b97d1b862',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/66e4fa55-fdac-4ef9-91b5-733b97d1b862',
        ], 201)]);
        $pesanan = $this->buatPesanan();

        $transaksi = (new MidtransGateway('SB-Mid-server-UJICOBA', 'SB-Mid-client-UJICOBA', false))->buatTransaksi($pesanan, $pesanan->nomor);

        $this->assertSame('66e4fa55-fdac-4ef9-91b5-733b97d1b862', $transaksi->token);
        Http::assertSent(function (Request $request) use ($pesanan) {
            $body = $request->data();

            return $request->url() === 'https://app.sandbox.midtrans.com/snap/v1/transactions'
                && $request->method() === 'POST'
                && $request->header('Authorization')[0] === 'Basic '.base64_encode('SB-Mid-server-UJICOBA:')
                && $body['transaction_details'] === ['order_id' => $pesanan->nomor, 'gross_amount' => 90000]
                && $body['item_details'] === [['id' => 'SMP-PDN-250', 'price' => 45000, 'quantity' => 2, 'name' => 'Semprong Pandan Toples kecil']]
                && $body['customer_details']['email'] === 'budi@example.com'
                && $body['customer_details']['shipping_address']['postal_code'] === '40111'
                && $body['expiry'] === ['start_time' => '2026-10-26 10:00:00 +0700', 'unit' => 'minute', 'duration' => 1440]
                && $body['callbacks']['finish'] === route('pesanan.detail', $pesanan);
        });
    }

    public function test_mode_produksi_memakai_server_produksi()
    {
        Http::fake(['app.midtrans.com/*' => Http::response(['token' => 't', 'redirect_url' => 'u'], 201)]);

        (new MidtransGateway('Mid-server-ASLI', 'Mid-client-ASLI', true))->buatTransaksi($pesanan = $this->buatPesanan(), $pesanan->nomor);

        Http::assertSent(fn (Request $request) => $request->url() === 'https://app.midtrans.com/snap/v1/transactions');
    }

    public function test_penolakan_midtrans_menjadi_exception()
    {
        Http::fake(['*' => Http::response(['error_messages' => ['Access denied due to unauthorized transaction']], 401)]);

        $this->expectException(RuntimeException::class);

        (new MidtransGateway('kunci-salah', '', false))->buatTransaksi($pesanan = $this->buatPesanan(), $pesanan->nomor);
    }

    public function test_capture_kartu_yang_ditandai_challenge_belum_dianggap_dibayar()
    {
        $gateway = new MidtransGateway('SB-Mid-server-UJICOBA', 'SB-Mid-client-UJICOBA', false);
        $payload = [
            'order_id' => 'SMP-20261026-0001', 'status_code' => '200', 'gross_amount' => '90000.00',
            'transaction_status' => 'capture', 'fraud_status' => 'challenge', 'transaction_id' => 'abc',
            'payment_type' => 'credit_card',
        ];
        $payload['signature_key'] = hash('sha512', 'SMP-20261026-0001'.'200'.'90000.00'.'SB-Mid-server-UJICOBA');

        $this->assertSame(KejadianPembayaran::Tertunda, $gateway->bacaNotifikasi($payload)->kejadian);

        $payload['fraud_status'] = 'accept';
        $notifikasi = $gateway->bacaNotifikasi($payload);
        $this->assertSame(KejadianPembayaran::Dibayar, $notifikasi->kejadian);
        $this->assertSame(90000, $notifikasi->nominal);
    }
}

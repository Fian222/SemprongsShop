<?php

namespace Tests\Feature;

use App\Enums\StatusPesanan;
use App\Exceptions\TransisiTidakSah;
use App\Mail\PembayaranDiterima;
use App\Mail\PesananDikirim;
use App\Models\Keranjang;
use App\Models\Pesanan;
use App\Models\User;
use App\Models\Varian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class NotifikasiEmailTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    protected Varian $varian;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $this->varian = Varian::factory()->create(['harga' => 45000, 'stok' => 10]);
    }

    protected function buatPesanan(): Pesanan
    {
        $user = User::factory()->create(['email' => 'budi@example.com']);
        $keranjang = Keranjang::create(['user_id' => $user->id]);
        $keranjang->tambah($this->varian, 2);

        return $this->pesananDari($keranjang, $this->alamatUntuk($user));
    }

    // notifikasi pembayaran lewat jendela simulasi, jalur yang sama dengan webhook gateway
    protected function bayar(Pesanan $pesanan)
    {
        return $this->actingAs($pesanan->user)->post(route('pembayaran.simulasi', $pesanan), ['status' => 'settlement']);
    }

    public function test_email_pembayaran_diterima_terkirim_sekali_walau_notifikasinya_berulang()
    {
        $pesanan = $this->buatPesanan();

        $this->bayar($pesanan);
        $this->bayar($pesanan);

        $this->assertSame(StatusPesanan::Dibayar, $pesanan->fresh()->status);
        Mail::assertQueuedCount(1);
        Mail::assertQueued(PembayaranDiterima::class, fn ($mail) => $mail->hasTo('budi@example.com')
            && $mail->pesanan->is($pesanan));
    }

    public function test_dua_pembayaran_yang_berebut_hanya_mengirim_satu_email()
    {
        $pesanan = $this->buatPesanan();
        // dua proses yang sama-sama membaca pesanan saat masih menunggu pembayaran
        $salinan = Pesanan::find($pesanan->id);

        $pesanan->ubahStatus(StatusPesanan::Dibayar);
        try {
            $salinan->ubahStatus(StatusPesanan::Dibayar);
            $this->fail('Transisi kedua seharusnya ditolak.');
        } catch (TransisiTidakSah) {
        }

        Mail::assertQueuedCount(1);
    }

    public function test_email_pembayaran_diterima_melampirkan_invoice_pdf()
    {
        $pesanan = $this->buatPesanan();

        $this->bayar($pesanan);

        Mail::assertQueued(PembayaranDiterima::class, function ($mail) use ($pesanan) {
            [$lampiran] = $mail->attachments();
            $isi = $lampiran->attachWith(fn ($path) => null, fn ($data) => $data());

            return $lampiran->as === "invoice-{$pesanan->nomor}.pdf" && $lampiran->mime === 'application/pdf'
                && str_starts_with($isi, '%PDF-');
        });
    }

    public function test_email_pesanan_dikirim_berisi_kurir_dan_resi()
    {
        $pesanan = $this->buatPesanan();
        $this->bayar($pesanan);
        $pesanan->ubahStatus(StatusPesanan::Diproses);

        $pesanan->kirim('JNE0001234');

        Mail::assertQueued(PesananDikirim::class, function ($mail) use ($pesanan) {
            $mail->assertSeeInHtml('JNE Reguler')->assertSeeInHtml('JNE0001234');

            return $mail->hasTo('budi@example.com') && $mail->pesanan->is($pesanan);
        });
    }

    public function test_resi_yang_ditolak_tidak_mengirim_email()
    {
        $pesanan = $this->buatPesanan();
        $this->bayar($pesanan);

        // belum Diproses, jadi belum bisa Dikirim
        try {
            $pesanan->kirim('JNE0001234');
        } catch (TransisiTidakSah) {
        }

        Mail::assertNotQueued(PesananDikirim::class);
    }
}

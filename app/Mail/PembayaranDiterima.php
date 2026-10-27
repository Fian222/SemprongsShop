<?php

namespace App\Mail;

use App\Models\Pesanan;
use App\Support\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PembayaranDiterima extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Pesanan $pesanan)
    {
        // transisi status yang batal tidak boleh sempat mengirim email
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Pembayaran diterima untuk pesanan {$this->pesanan->nomor}");
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.pembayaran-diterima');
    }

    // invoice sebagai bukti bayar, dibuat saat email dikirim dari antrean
    public function attachments(): array
    {
        $invoice = new Invoice($this->pesanan);

        return [Attachment::fromData(fn () => $invoice->pdf(), $invoice->namaFile())->withMime('application/pdf')];
    }
}

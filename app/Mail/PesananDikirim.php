<?php

namespace App\Mail;

use App\Models\Pesanan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PesananDikirim extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Pesanan $pesanan)
    {
        // transisi status yang batal tidak boleh sempat mengirim email
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Pesanan {$this->pesanan->nomor} sudah dikirim");
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.pesanan-dikirim');
    }
}

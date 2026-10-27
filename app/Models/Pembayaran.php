<?php

namespace App\Models;

use App\Enums\StatusPembayaran;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Transaksi di payment gateway untuk satu pesanan, beserta notifikasi terakhirnya
#[Fillable(['provider', 'referensi', 'transaksi_id', 'token', 'redirect_url', 'metode', 'status', 'nominal', 'payload', 'dibayar_at'])]
class Pembayaran extends Model
{
    protected function casts(): array
    {
        return ['status' => StatusPembayaran::class, 'nominal' => 'integer', 'payload' => 'array', 'dibayar_at' => 'datetime'];
    }

    // mis. "bank_transfer" menjadi "Bank Transfer"; null bila belum diketahui
    public function labelMetode(): ?string
    {
        return $this->metode ? (string) str($this->metode)->replace('_', ' ')->title() : null;
    }

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class);
    }
}

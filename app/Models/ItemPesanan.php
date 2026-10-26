<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Salinan varian saat dipesan; nama dan harga tidak ikut berubah bila katalog berubah
#[Fillable(['varian_id', 'nama_produk', 'nama_varian', 'sku', 'foto', 'harga', 'jumlah', 'berat'])]
class ItemPesanan extends Model
{
    protected function casts(): array
    {
        return ['harga' => 'integer', 'jumlah' => 'integer', 'berat' => 'integer'];
    }

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class);
    }

    public function varian(): BelongsTo
    {
        return $this->belongsTo(Varian::class);
    }

    public function jumlahHarga(): int
    {
        return $this->harga * $this->jumlah;
    }

    public function fotoUrl(): ?string
    {
        return $this->foto ? asset('build/'.$this->foto) : null;
    }
}

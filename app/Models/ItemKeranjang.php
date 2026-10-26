<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['varian_id', 'jumlah'])]
class ItemKeranjang extends Model
{
    protected function casts(): array
    {
        return ['jumlah' => 'integer'];
    }

    public function keranjang(): BelongsTo
    {
        return $this->belongsTo(Keranjang::class);
    }

    public function varian(): BelongsTo
    {
        return $this->belongsTo(Varian::class);
    }

    // harga varian × jumlah
    public function jumlahHarga(): int
    {
        return $this->varian->harga * $this->jumlah;
    }

    // alasan item ini belum bisa dipesan, atau null bila bisa
    public function kendala(): ?string
    {
        $varian = $this->varian;

        if (! $varian->dijual()) {
            return 'Sedang tidak dijual';
        }

        if ($varian->stok < $this->jumlah) {
            return $varian->habis() ? 'Stok habis' : "Stok tinggal {$varian->stok}";
        }

        return null;
    }
}

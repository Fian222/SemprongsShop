<?php

namespace App\Models;

use Database\Factories\VarianFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['produk_id', 'nama', 'sku', 'harga', 'harga_coret', 'stok', 'berat', 'aktif'])]
class Varian extends Model
{
    /** @use HasFactory<VarianFactory> */
    use HasFactory;

    // di bawah angka ini toko menandai stok sebagai menipis
    public const STOK_MENIPIS = 10;

    protected function casts(): array
    {
        return [
            'harga' => 'integer',
            'harga_coret' => 'integer',
            'stok' => 'integer',
            'berat' => 'integer',
            'aktif' => 'boolean',
        ];
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class);
    }

    public function habis(): bool
    {
        return $this->stok < 1;
    }

    public function menipis(): bool
    {
        return ! $this->habis() && $this->stok < self::STOK_MENIPIS;
    }

    // harga coret hanya berarti kalau lebih mahal dari harga jual
    public function punyaHargaCoret(): bool
    {
        return $this->harga_coret !== null && $this->harga_coret > $this->harga;
    }
}

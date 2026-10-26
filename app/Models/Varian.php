<?php

namespace App\Models;

use App\Exceptions\KeranjangDitolak;
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

    // varian aktif dari produk yang tampil di toko
    public function dijual(): bool
    {
        return $this->aktif && Produk::tampil()->whereKey($this->produk_id)->exists();
    }

    /**
     * Pastikan $jumlah varian ini bisa dipesan, ditambah yang sudah ada di keranjang.
     *
     * @throws KeranjangDitolak
     */
    public function pastikanBisaDipesan(int $jumlah, int $diKeranjang = 0): void
    {
        if (! $this->dijual()) {
            throw KeranjangDitolak::tidakDijual($this);
        }

        if ($diKeranjang + $jumlah > $this->stok) {
            throw KeranjangDitolak::stokKurang($this, $diKeranjang);
        }
    }

    // harga coret hanya berarti kalau lebih mahal dari harga jual
    public function punyaHargaCoret(): bool
    {
        return $this->harga_coret !== null && $this->harga_coret > $this->harga;
    }
}

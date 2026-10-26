<?php

namespace App\Models;

use Database\Factories\ProdukFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['kategori_id', 'nama', 'slug', 'deskripsi', 'komposisi', 'aktif', 'unggulan'])]
class Produk extends Model
{
    /** @use HasFactory<ProdukFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['aktif' => 'boolean', 'unggulan' => 'boolean'];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    public function varians(): HasMany
    {
        return $this->hasMany(Varian::class)->orderBy('harga')->orderBy('id');
    }

    public function varianAktif(): HasMany
    {
        return $this->varians()->where('aktif', true);
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(FotoProduk::class)->orderBy('urutan')->orderBy('id');
    }

    public function fotoUtama(): HasOne
    {
        return $this->hasOne(FotoProduk::class)->ofMany(['urutan' => 'min', 'id' => 'min']);
    }

    /**
     * Produk yang tampil di toko: aktif, kategorinya aktif, dan punya minimal satu varian aktif.
     */
    public function scopeTampil(Builder $query): void
    {
        $query->where('aktif', true)
            ->whereHas('kategori', fn (Builder $q) => $q->where('aktif', true))
            ->whereHas('varianAktif');
    }

    /**
     * Kolom harga_mulai (varian aktif termurah) dan stok_tersedia (jumlah stok varian aktif) untuk kartu produk.
     */
    public function scopeUntukKartu(Builder $query): void
    {
        $query->with('fotoUtama')
            ->withMin('varianAktif as harga_mulai', 'harga')
            ->withSum('varianAktif as stok_tersedia', 'stok');
    }

    public function habis(): bool
    {
        return (int) $this->stok_tersedia < 1;
    }
}

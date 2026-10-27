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
     * Kolom harga_mulai (varian aktif termurah), harga_coret_mulai (harga coret varian itu), dan stok_tersedia
     * (jumlah stok varian aktif) untuk kartu produk.
     */
    public function scopeUntukKartu(Builder $query): void
    {
        $query->with('fotoUtama')
            ->withMin('varianAktif as harga_mulai', 'harga')
            ->addSelect(['harga_coret_mulai' => Varian::select('harga_coret')->whereColumn('produk_id', 'produks.id')
                ->where('aktif', true)->orderBy('harga')->orderBy('id')->limit(1)])
            ->withSum('varianAktif as stok_tersedia', 'stok');
    }

    // harga coret di kartu produk, hanya bila lebih mahal dari harga mulai
    public function hargaCoretMulai(): ?int
    {
        return $this->harga_coret_mulai > $this->harga_mulai ? (int) $this->harga_coret_mulai : null;
    }

    public function habis(): bool
    {
        return (int) $this->stok_tersedia < 1;
    }
}

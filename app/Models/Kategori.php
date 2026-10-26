<?php

namespace App\Models;

use Database\Factories\KategoriFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'slug', 'urutan', 'aktif'])]
class Kategori extends Model
{
    /** @use HasFactory<KategoriFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['aktif' => 'boolean', 'urutan' => 'integer'];
    }

    public function produks(): HasMany
    {
        return $this->hasMany(Produk::class);
    }

    public function scopeAktif(Builder $query): void
    {
        $query->where('aktif', true);
    }

    public function scopeTerurut(Builder $query): void
    {
        $query->orderBy('urutan')->orderBy('nama');
    }
}

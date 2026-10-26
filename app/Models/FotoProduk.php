<?php

namespace App\Models;

use Database\Factories\FotoProdukFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['produk_id', 'path', 'urutan'])]
class FotoProduk extends Model
{
    /** @use HasFactory<FotoProdukFactory> */
    use HasFactory;

    // disk tempat foto disimpan; root-nya public/build, jadi URL-nya /build/{path}
    public const DISK = 'public_uploads';

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class);
    }

    public function url(): string
    {
        return asset('build/'.$this->path);
    }
}

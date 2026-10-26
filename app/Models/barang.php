<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nama_barang', 'harga_barang', 'stok_barang', 'gambar', 'id_admin'])]
class barang extends Model
{
    // di bawah angka ini toko menandai stok sebagai menipis
    public const STOK_MENIPIS = 10;

    public function habis(): bool
    {
        return $this->stok_barang < 1;
    }

    public function menipis(): bool
    {
        return ! $this->habis() && $this->stok_barang < self::STOK_MENIPIS;
    }

    public function pesanan()
    {
        return $this->hasMany(pesanan::class, 'id_barang', 'id');
    }

    public function pembayaran()
    {
        return $this->hasMany(pembayaran::class, 'id_barang', 'id');
    }
}

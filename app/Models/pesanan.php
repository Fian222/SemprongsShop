<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['id_barang', 'jumlah_barang', 'id_user', 'total_harga'])]
class pesanan extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function pembayaran()
    {
        return $this->hasMany(pembayaran::class, 'id_pesanan', 'id');
    }

    public function pesanandetail()
    {
        return $this->hasMany(pesanandetail::class, 'id_pesanan', 'id');
    }
}

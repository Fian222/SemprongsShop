<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['id_barang', 'id_pesanan', 'jumlah_pesanan', 'id_user', 'total_harga'])]
class pesanandetail extends Model
{
    public function pesanan()
    {
        return $this->belongsTo(pesanan::class, 'id_pesanan', 'id');
    }

    public function barang()
    {
        return $this->belongsTo(barang::class, 'id_barang', 'id');
    }
}

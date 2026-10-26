<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Item pesanan lama. Menunjuk ke varian sampai keranjang (#14) dan checkout (#15) menggantikannya.
#[Fillable(['id_varian', 'id_pesanan', 'jumlah_pesanan', 'id_user', 'total_harga'])]
class pesanandetail extends Model
{
    public function pesanan()
    {
        return $this->belongsTo(pesanan::class, 'id_pesanan', 'id');
    }

    public function varian()
    {
        return $this->belongsTo(Varian::class, 'id_varian', 'id');
    }
}

<?php

namespace App\Models;

use App\Pengiriman\Tarif;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Kurir dan layanan yang dipilih untuk satu pesanan, beserta ongkir saat dipesan
#[Table('pengirimans')]
#[Fillable(['kurir', 'nama_kurir', 'layanan', 'nama_layanan', 'ongkir', 'estimasi', 'berat'])]
class Pengiriman extends Model
{
    protected function casts(): array
    {
        return ['ongkir' => 'integer', 'berat' => 'integer'];
    }

    public static function dariTarif(Tarif $tarif): self
    {
        return new self([
            'kurir' => $tarif->kurir,
            'nama_kurir' => $tarif->namaKurir,
            'layanan' => $tarif->layanan,
            'nama_layanan' => $tarif->namaLayanan,
            'ongkir' => $tarif->ongkir,
            'estimasi' => $tarif->estimasi,
            'berat' => $tarif->berat,
        ]);
    }

    // mis. "JNE Reguler"
    public function label(): string
    {
        return $this->nama_kurir.' '.$this->nama_layanan;
    }

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class);
    }
}

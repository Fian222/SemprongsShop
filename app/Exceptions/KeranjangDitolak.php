<?php

namespace App\Exceptions;

use App\Models\Varian;
use DomainException;

// Perubahan keranjang yang tidak bisa dipenuhi; pesannya siap ditampilkan ke pembeli
class KeranjangDitolak extends DomainException
{
    public static function tidakDijual(Varian $varian): self
    {
        return new self("{$varian->produk->nama} {$varian->nama} sedang tidak dijual.");
    }

    public static function stokKurang(Varian $varian, int $diKeranjang = 0): self
    {
        if ($varian->habis()) {
            return new self("Stok {$varian->nama} sedang habis.");
        }

        return new self("Stok {$varian->nama} tinggal {$varian->stok}"
            .($diKeranjang ? ", dan {$diKeranjang} sudah ada di tas belanjamu." : '.'));
    }
}

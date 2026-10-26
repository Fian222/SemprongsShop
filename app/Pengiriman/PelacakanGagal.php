<?php

namespace App\Pengiriman;

use RuntimeException;

// Riwayat pelacakan tidak bisa dimuat; pesannya aman ditampilkan ke pembeli
class PelacakanGagal extends RuntimeException
{
    // resi belum dikenal kurir, provider tidak bisa dihubungi, atau menjawab dengan bentuk yang tidak dikenal
    public static function provider(string $detail): self
    {
        return new self('Riwayat pelacakan belum bisa dimuat. Resi baru biasanya bisa dilacak beberapa jam sesudah paket diserahkan ke kurir.',
            previous: new RuntimeException($detail));
    }
}

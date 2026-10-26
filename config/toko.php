<?php

return [

    /*
    | Rekening tujuan transfer manual. Dipakai sampai pembayaran Midtrans (#15) menggantikannya.
    */
    'rekening' => [
        'bank' => env('TOKO_REKENING_BANK', 'BCA'),
        'nomor' => env('TOKO_REKENING_NOMOR', '12722993'),
        'nama' => env('TOKO_REKENING_NAMA', 'Akhmat Oktafian'),
    ],

];

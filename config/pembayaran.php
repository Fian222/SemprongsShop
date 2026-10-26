<?php

return [

    /*
    | Adapter PaymentGateway: 'midtrans' (Snap), atau 'simulasi' untuk pengembangan tanpa akun Midtrans.
    | Simulasi ditolak saat APP_ENV=production.
    */
    'driver' => env('PEMBAYARAN_DRIVER', 'midtrans'),

    // pesanan yang belum dibayar sampai lewat batas ini menjadi kedaluwarsa
    'batas_bayar_jam' => 24,

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'produksi' => (bool) env('MIDTRANS_PRODUKSI', false),
    ],

];

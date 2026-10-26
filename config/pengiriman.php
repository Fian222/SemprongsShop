<?php

return [

    /*
    | Adapter ShippingProvider: 'biteship', atau 'simulasi' untuk pengembangan tanpa akun Biteship.
    | Simulasi ditolak saat APP_ENV=production.
    */
    'driver' => env('PENGIRIMAN_DRIVER', 'biteship'),

    'biteship' => [
        'api_key' => env('BITESHIP_API_KEY'),
        // ID area Biteship tempat paket dikirim (dapur toko), dari pencarian area di API maps
        'area_asal' => env('BITESHIP_AREA_ASAL'),
        // kode kurir Biteship yang ditawarkan di checkout
        'kurir' => env('BITESHIP_KURIR', 'jne,sicepat,jnt,anteraja,pos'),
    ],

];

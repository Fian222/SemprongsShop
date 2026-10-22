<?php

namespace App\Support;

use Carbon\Carbon;

class Format
{
    public static function rupiah($nilai): string
    {
        return 'Rp '.number_format((float) $nilai, 0, ',', '.');
    }

    public static function tanggal($waktu): string
    {
        return Carbon::parse($waktu)->locale('id')->translatedFormat('j M Y, H:i');
    }
}

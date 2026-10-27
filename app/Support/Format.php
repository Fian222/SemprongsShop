<?php

namespace App\Support;

use Carbon\Carbon;

class Format
{
    public static function rupiah($nilai): string
    {
        return 'Rp '.number_format((float) $nilai, 0, ',', '.');
    }

    // label sumbu grafik: Rp 0, Rp 750 rb, Rp 1,5 jt
    public static function rupiahRingkas(int $nilai): string
    {
        return match (true) {
            $nilai >= 1_000_000 => 'Rp '.number_format($nilai / 1_000_000, $nilai % 1_000_000 ? 1 : 0, ',', '.').' jt',
            $nilai >= 1_000 => 'Rp '.number_format($nilai / 1_000, $nilai % 1_000 ? 1 : 0, ',', '.').' rb',
            default => 'Rp '.$nilai,
        };
    }

    public static function tanggal($waktu): string
    {
        return Carbon::parse($waktu)->locale('id')->translatedFormat('j M Y, H:i');
    }
}

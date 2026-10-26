<?php

namespace App\Pembayaran;

enum KejadianPembayaran
{
    case Dibayar;
    case Kedaluwarsa;
    case Dibatalkan;
    case Tertunda;
}

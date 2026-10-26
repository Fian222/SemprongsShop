<?php

namespace App\Pembayaran;

final readonly class TransaksiBaru
{
    public function __construct(public string $token, public string $redirectUrl) {}
}

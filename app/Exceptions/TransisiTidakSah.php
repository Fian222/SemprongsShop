<?php

namespace App\Exceptions;

use App\Enums\StatusPesanan;
use DomainException;

class TransisiTidakSah extends DomainException
{
    public function __construct(public StatusPesanan $dari, public StatusPesanan $ke, ?string $pesan = null)
    {
        parent::__construct($pesan ?? "Pesanan {$dari->label()} tidak bisa menjadi {$ke->label()}.");
    }
}

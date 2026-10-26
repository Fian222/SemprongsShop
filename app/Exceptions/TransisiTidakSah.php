<?php

namespace App\Exceptions;

use App\Enums\StatusPesanan;
use DomainException;

class TransisiTidakSah extends DomainException
{
    public function __construct(public StatusPesanan $dari, public StatusPesanan $ke)
    {
        parent::__construct("Pesanan {$dari->label()} tidak bisa menjadi {$ke->label()}.");
    }
}

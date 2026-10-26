<?php

namespace App\Pengiriman;

final readonly class Area
{
    public function __construct(public string $id, public string $nama, public ?string $kodePos = null) {}

    public function keArray(): array
    {
        return ['id' => $this->id, 'nama' => $this->nama, 'kode_pos' => $this->kodePos];
    }
}

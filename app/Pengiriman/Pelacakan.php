<?php

namespace App\Pengiriman;

use Carbon\CarbonImmutable;

// Posisi terakhir paket dan riwayat perjalanannya, terbaru di atas
final readonly class Pelacakan
{
    /**
     * @param  string  $status  keadaan terakhir untuk ditampilkan, mis. "Paket sedang diantar"
     * @param  bool  $sampai  paket sudah diterima di alamat tujuan
     * @param  list<KejadianLacak>  $riwayat
     */
    public function __construct(public string $status, public bool $sampai, public array $riwayat) {}

    // kebalikan keArray()
    public static function dariArray(array $data): self
    {
        return new self($data['status'], $data['sampai'], array_map(
            fn (array $k) => new KejadianLacak(CarbonImmutable::parse($k['waktu']), $k['keterangan']), $data['riwayat']));
    }

    // untuk state komponen Livewire dan cache, yang tidak menyimpan objek
    public function keArray(): array
    {
        return [
            'status' => $this->status,
            'sampai' => $this->sampai,
            'riwayat' => array_map(fn (KejadianLacak $k) => ['waktu' => $k->waktu->toIso8601String(), 'keterangan' => $k->keterangan], $this->riwayat),
        ];
    }
}

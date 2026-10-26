<?php

namespace App\Livewire;

use App\Models\Kategori;
use App\Models\Produk;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Katalog extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public $cari = '';

    // slug kategori; kosong berarti semua kategori
    #[Url(except: '')]
    public $kategori = '';

    public const URUTAN = [
        'terbaru' => 'Terbaru',
        'harga-terendah' => 'Harga terendah',
        'harga-tertinggi' => 'Harga tertinggi',
    ];

    #[Url(except: 'terbaru')]
    public $urut = 'terbaru';

    public function updating($properti)
    {
        if (in_array($properti, ['cari', 'kategori', 'urut'])) {
            $this->resetPage();
        }
    }

    public function semuaProduk()
    {
        $this->reset('cari', 'kategori');
        $this->resetPage();
    }

    public function render()
    {
        $produks = Produk::tampil()->untukKartu()
            ->when($this->cari, fn ($q) => $q->where('nama', 'like', '%'.$this->cari.'%'))
            ->when($this->kategori, fn ($q) => $q->whereRelation('kategori', 'slug', $this->kategori))
            ->tap(fn ($q) => match ($this->urut) {
                'harga-terendah' => $q->orderBy('harga_mulai'),
                'harga-tertinggi' => $q->orderByDesc('harga_mulai'),
                default => null,
            })
            ->latest('id')
            ->paginate(12);

        return view('livewire.katalog', [
            'produks' => $produks,
            'kategoris' => Kategori::aktif()->terurut()->get(),
            'urutan' => self::URUTAN,
        ])
            ->extends('layouts.app')
            ->section('content');
    }
}

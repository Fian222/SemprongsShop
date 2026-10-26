<?php

namespace App\Livewire\Admin;

use App\Enums\StatusPesanan;
use App\Models\Pesanan;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class DaftarPesanan extends Component
{
    use WithPagination;

    // nilai StatusPesanan; kosong berarti semua status
    #[Url(except: '')]
    public $status = '';

    // nomor pesanan atau nama pemesan
    #[Url(except: '')]
    public $cari = '';

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingCari()
    {
        $this->resetPage();
    }

    public function render()
    {
        $status = StatusPesanan::tryFrom((string) $this->status);
        $cari = trim((string) $this->cari);

        $pesanans = Pesanan::with('user', 'items', 'pengiriman', 'pembayaran')
            ->when($status, fn ($q) => $q->status($status))
            ->when($cari !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('nomor', 'like', '%'.$cari.'%')
                ->orWhereRelation('user', 'name', 'like', '%'.$cari.'%')))
            ->latest('id')
            ->paginate(20);

        return view('livewire.admin.daftar-pesanan', [
            'pesanans' => $pesanans,
            'aktif' => $status,
            'jumlah' => Pesanan::toBase()->selectRaw('status, count(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status'),
        ])
            ->extends('layouts.adminapp')
            ->section('content');
    }
}

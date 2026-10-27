<?php

namespace App\Livewire\Admin;

use App\Enums\StatusPesanan;
use App\Livewire\Forms\VoucherForm;
use App\Models\Voucher;
use Livewire\Component;

class KelolaVoucher extends Component
{
    public VoucherForm $form;

    public function ubah($id)
    {
        $this->form->isi(Voucher::findOrFail($id));
        $this->resetValidation();
    }

    public function batal()
    {
        $this->form->reset();
        $this->resetValidation();
    }

    public function simpan()
    {
        $baru = ! $this->form->voucher;
        $voucher = $this->form->simpan();

        session()->flash('message', $baru ? "Voucher {$voucher->kode} ditambahkan." : "Voucher {$voucher->kode} disimpan.");
    }

    // voucher yang sudah tercatat di pesanan dinonaktifkan saja, supaya riwayat promonya tetap terbaca
    public function hapus($id)
    {
        $voucher = Voucher::findOrFail($id);
        if ($voucher->pesanans()->exists()) {
            return $this->addError('hapus', "Voucher {$voucher->kode} sudah dipakai pesanan. Nonaktifkan saja supaya tidak bisa dipakai lagi.");
        }

        $voucher->delete();
        if ($this->form->voucher?->is($voucher)) {
            $this->form->reset();
        }

        session()->flash('message', "Voucher {$voucher->kode} dihapus.");
    }

    public function render()
    {
        return view('livewire.admin.kelola-voucher', [
            'vouchers' => Voucher::denganPemakaian()
                ->withCount(['pesanans as menunggu' => fn ($q) => $q->status(StatusPesanan::MenungguPembayaran)])
                ->latest()->orderByDesc('id')->get(),
        ])
            ->extends('layouts.adminapp')
            ->section('content');
    }
}

<?php

namespace App\Livewire\Forms;

use App\Enums\TipeVoucher;
use App\Models\Voucher;
use Illuminate\Validation\Rule;
use Livewire\Form;

class VoucherForm extends Form
{
    public ?Voucher $voucher = null;

    public $kode = '';

    public $tipe = 'nominal';

    public $nilai = '';

    public $maks_diskon = '';

    public $min_subtotal = 0;

    public $kuota = '';

    // nilai input datetime-local, mis. 2026-11-01T00:00
    public $mulai = '';

    public $berakhir = '';

    public $aktif = true;

    public function persen(): bool
    {
        return $this->tipe === TipeVoucher::Persen->value;
    }

    public function isi(Voucher $voucher): void
    {
        $this->voucher = $voucher;
        $this->fill($voucher->only('kode', 'nilai', 'maks_diskon', 'min_subtotal', 'kuota', 'aktif'));
        $this->tipe = $voucher->tipe->value;
        $this->mulai = $voucher->mulai?->format('Y-m-d\TH:i') ?? '';
        $this->berakhir = $voucher->berakhir?->format('Y-m-d\TH:i') ?? '';
    }

    public function simpan(): Voucher
    {
        $this->kode = Voucher::normalisasiKode($this->kode);
        // batas maksimal hanya berarti untuk diskon persen
        if (! $this->persen()) {
            $this->maks_diskon = '';
        }
        foreach (['maks_diskon', 'kuota', 'mulai', 'berakhir'] as $kolom) {
            if ($this->{$kolom} === '') {
                $this->{$kolom} = null;
            }
        }

        $data = $this->validate([
            'kode' => ['required', 'string', 'max:30', 'regex:/^[A-Z0-9_-]+$/', Rule::unique('vouchers', 'kode')->ignore($this->voucher)],
            'tipe' => ['required', Rule::enum(TipeVoucher::class)],
            'nilai' => ['required', 'integer', 'min:1', Rule::when($this->persen(), 'max:100')],
            'maks_diskon' => ['nullable', 'integer', 'min:1'],
            'min_subtotal' => ['required', 'integer', 'min:0'],
            'kuota' => ['nullable', 'integer', 'min:1'],
            'mulai' => ['nullable', 'date'],
            'berakhir' => ['nullable', 'date', Rule::when(filled($this->mulai), 'after:mulai')],
            'aktif' => ['boolean'],
        ], [
            'kode.unique' => 'Kode ini sudah dipakai voucher lain.',
            'kode.regex' => 'Kode hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
            'nilai.max' => 'Diskon persen paling besar 100.',
            'berakhir.after' => 'Waktu berakhir harus sesudah waktu mulai.',
        ]);

        $voucher = $this->voucher ?? new Voucher;
        $voucher->fill($data)->save();

        $this->reset();

        return $voucher;
    }
}

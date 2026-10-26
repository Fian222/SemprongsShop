<?php

namespace App\Livewire\Forms;

use App\Models\Produk;
use App\Models\Varian;
use Illuminate\Validation\Rule;
use Livewire\Form;

class VarianForm extends Form
{
    public ?Varian $varian = null;

    public $nama = '';

    public $sku = '';

    public $harga = '';

    public $harga_coret = '';

    public $stok = 0;

    public $berat = '';

    public $aktif = true;

    public function isi(Varian $varian): void
    {
        $this->varian = $varian;
        $this->fill($varian->only('nama', 'sku', 'harga', 'harga_coret', 'stok', 'berat', 'aktif'));
    }

    public function simpan(Produk $produk): Varian
    {
        $this->sku = strtoupper(trim($this->sku));
        if ($this->harga_coret === '') {
            $this->harga_coret = null;
        }

        $data = $this->validate([
            'nama' => ['required', 'string', 'max:100'],
            'sku' => ['required', 'string', 'max:50', Rule::unique('varians', 'sku')->ignore($this->varian)],
            'harga' => ['required', 'integer', 'min:1'],
            'harga_coret' => ['nullable', 'integer', 'gt:harga'],
            'stok' => ['required', 'integer', 'min:0'],
            'berat' => ['required', 'integer', 'min:1'],
            'aktif' => ['boolean'],
        ], [
            'sku.unique' => 'SKU ini sudah dipakai varian lain.',
            'harga_coret.gt' => 'Harga coret harus lebih mahal dari harga.',
        ]);

        $varian = $this->varian ?? $produk->varians()->make();
        $varian->fill($data)->save();

        $this->reset();

        return $varian;
    }
}

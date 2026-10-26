<?php

namespace App\Livewire\Forms;

use App\Models\Produk;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ProdukForm extends Form
{
    public ?Produk $produk = null;

    public $kategori_id = '';

    public $nama = '';

    public $slug = '';

    public $deskripsi = '';

    public $komposisi = '';

    public $aktif = true;

    public $unggulan = false;

    public function isi(Produk $produk): void
    {
        $this->produk = $produk;
        $this->fill($produk->only('kategori_id', 'nama', 'slug', 'deskripsi', 'komposisi', 'aktif', 'unggulan'));
    }

    public function simpan(): Produk
    {
        // slug kosong diturunkan dari nama
        $this->slug = Str::slug($this->slug ?: $this->nama);

        $data = $this->validate([
            'kategori_id' => ['required', 'exists:kategoris,id'],
            'nama' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:150', Rule::unique('produks', 'slug')->ignore($this->produk)],
            'deskripsi' => ['required', 'string', 'max:5000'],
            'komposisi' => ['nullable', 'string', 'max:2000'],
            'aktif' => ['boolean'],
            'unggulan' => ['boolean'],
        ], [
            'kategori_id.required' => 'Pilih kategori produk.',
            'slug.unique' => 'Slug ini sudah dipakai produk lain.',
        ]);

        $produk = $this->produk ?? new Produk;
        $produk->fill($data)->save();
        $this->produk = $produk;

        return $produk;
    }
}

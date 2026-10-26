<?php

namespace App\Livewire\Forms;

use App\Models\Kategori;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Form;

class KategoriForm extends Form
{
    public ?Kategori $kategori = null;

    public $nama = '';

    public $slug = '';

    public $urutan = 0;

    public $aktif = true;

    public function isi(Kategori $kategori): void
    {
        $this->kategori = $kategori;
        $this->fill($kategori->only('nama', 'slug', 'urutan', 'aktif'));
    }

    public function simpan(): Kategori
    {
        // slug kosong diturunkan dari nama
        $this->slug = Str::slug($this->slug ?: $this->nama);

        $data = $this->validate([
            'nama' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', Rule::unique('kategoris', 'slug')->ignore($this->kategori)],
            'urutan' => ['required', 'integer', 'min:0'],
            'aktif' => ['boolean'],
        ], [
            'slug.unique' => 'Slug ini sudah dipakai kategori lain.',
        ]);

        $kategori = $this->kategori ?? new Kategori;
        $kategori->fill($data)->save();

        $this->reset();

        return $kategori;
    }
}

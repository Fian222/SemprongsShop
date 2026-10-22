<?php

namespace App\Livewire;

use App\Models\admin;
use App\Models\barang;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Tambahbarang extends Component
{
    use WithFileUploads;
    public $gambar, $nama, $harga, $stok;

    public function tambah()
    {
        $this->validate([
            'gambar' => 'image|max:1024|required',
            'nama' => 'required',
            'harga' => 'required',
            'stok' => 'required',
        ]);

        $filename = $this->nama . '.' . $this->gambar->extension();
        $this->gambar->storeAs('images', $filename, 'public_uploads');
        barang::create([
            'id_admin' => Auth::guard('adminMiddle')->user()->id,
            'nama_barang' => $this->nama,
            'harga_barang' => $this->harga,
            'stok_barang' => $this->stok,
            'gambar' => $filename,
        ]);

        session()->flash('message', 'Berhasil tambah barang');
    }

    public function render()
    {
        return view('livewire.tambahbarang')
            ->extends('layouts.adminapp')
            ->section('content');
    }
}

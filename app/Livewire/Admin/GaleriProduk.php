<?php

namespace App\Livewire\Admin;

use App\Models\FotoProduk;
use App\Models\Produk;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

// Galeri foto satu produk, ditanam di halaman ubah produk. Foto pertama menjadi foto utama di kartu produk.
class GaleriProduk extends Component
{
    use WithFileUploads;

    public Produk $produk;

    public $unggahan = [];

    public function unggah()
    {
        $this->validate([
            'unggahan' => ['required', 'array', 'max:10'],
            'unggahan.*' => ['image', 'max:2048'],
        ], [
            'unggahan.required' => 'Pilih minimal satu foto.',
            'unggahan.*.image' => 'File :position bukan gambar.',
            'unggahan.*.max' => 'Foto :position lebih dari 2 MB.',
        ]);

        // foto baru masuk di belakang
        $urutan = $this->produk->fotos()->exists() ? $this->produk->fotos()->max('urutan') + 1 : 0;
        foreach ($this->unggahan as $file) {
            $this->produk->fotos()->create([
                'path' => $file->store('images/produk', FotoProduk::DISK),
                'urutan' => $urutan++,
            ]);
        }

        $this->reset('unggahan');
        session()->flash('message', 'Foto ditambahkan.');
    }

    // $arah -1 memajukan foto satu posisi, 1 memundurkannya
    public function geser($id, $arah)
    {
        $fotos = $this->produk->fotos()->get();
        $posisi = $fotos->search(fn (FotoProduk $foto) => $foto->id === (int) $id);
        abort_if($posisi === false, 404);

        $tujuan = $posisi + ($arah < 0 ? -1 : 1);
        if ($tujuan < 0 || $tujuan >= $fotos->count()) {
            return;
        }

        $urut = $fotos->all();
        [$urut[$posisi], $urut[$tujuan]] = [$urut[$tujuan], $urut[$posisi]];
        foreach ($urut as $i => $foto) {
            $foto->update(['urutan' => $i]);
        }
    }

    public function hapus($id)
    {
        $foto = $this->produk->fotos()->findOrFail($id);
        Storage::disk(FotoProduk::DISK)->delete($foto->path);
        $foto->delete();

        session()->flash('message', 'Foto dihapus.');
    }

    public function render()
    {
        return view('livewire.admin.galeri-produk', [
            'fotos' => $this->produk->fotos()->get(),
        ]);
    }
}

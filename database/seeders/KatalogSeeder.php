<?php

namespace Database\Seeders;

use App\Models\FotoProduk;
use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Data contoh untuk pengembangan. Aman dijalankan ulang: baris dicocokkan lewat slug dan SKU.
class KatalogSeeder extends Seeder
{
    protected const FOTO = 'images/produk/contoh-semprong.jpg';

    public function run(): void
    {
        $this->siapkanFoto();

        $katalog = [
            ['Semprong original', [
                ['Semprong Original', true, 'Gulungan tipis bermotif dari adonan tepung beras dan santan, dipanggang di atas bara.', 'Tepung beras, santan kelapa, gula pasir, telur, wijen', [
                    ['Toples kecil', 'ORI-250', 45000, null, 30, 300],
                    ['Toples besar', 'ORI-500', 80000, 90000, 18, 560],
                ]],
                ['Semprong Wijen Hitam', false, 'Taburan wijen hitam sangrai memberi rasa gurih dan aroma kacang.', 'Tepung beras, santan kelapa, gula pasir, telur, wijen hitam', [
                    ['Toples kecil', 'WJN-250', 48000, null, 6, 300],
                    ['Toples besar', 'WJN-500', 85000, null, 0, 560],
                ]],
                ['Semprong Pandan', false, 'Wangi daun pandan segar dengan warna hijau alami.', 'Tepung beras, santan kelapa, gula pasir, telur, sari daun pandan', [
                    ['Toples kecil', 'PDN-250', 47000, null, 22, 300],
                ]],
            ]],
            ['Semprong isi', [
                ['Semprong Isi Coklat', true, 'Gulungan renyah berisi krim coklat hitam yang lumer.', "Tepung beras, santan kelapa, gula pasir, telur, coklat hitam\nMengandung susu", [
                    ['Toples kecil', 'CKL-250', 55000, 62000, 25, 320],
                    ['Toples besar', 'CKL-500', 98000, null, 12, 600],
                ]],
                ['Semprong Isi Keju', false, 'Isian keju cheddar asin manis.', "Tepung beras, santan kelapa, gula pasir, telur, keju cheddar\nMengandung susu", [
                    ['Toples kecil', 'KJU-250', 58000, null, 9, 320],
                ]],
            ]],
            ['Hampers', [
                ['Hampers Pita Marun', true, 'Tiga toples semprong pilihan dalam kotak kain marun berpita champagne, lengkap dengan kartu ucapan.', 'Semprong original, semprong pandan, semprong isi coklat', [
                    ['Kotak kertas', 'HMP-KRT', 175000, null, 15, 1400],
                    ['Kotak kayu', 'HMP-KYU', 245000, 275000, 5, 2100],
                ]],
            ]],
        ];

        foreach ($katalog as $urutan => [$namaKategori, $produks]) {
            $kategori = Kategori::updateOrCreate(['slug' => Str::slug($namaKategori)], ['nama' => $namaKategori, 'urutan' => $urutan]);

            foreach ($produks as [$nama, $unggulan, $deskripsi, $komposisi, $varians]) {
                $produk = Produk::updateOrCreate(['slug' => Str::slug($nama)], [
                    'kategori_id' => $kategori->id,
                    'nama' => $nama,
                    'deskripsi' => $deskripsi,
                    'komposisi' => $komposisi,
                    'unggulan' => $unggulan,
                ]);

                foreach ($varians as [$namaVarian, $sku, $harga, $hargaCoret, $stok, $berat]) {
                    $produk->varians()->updateOrCreate(['sku' => 'SMP-'.$sku], [
                        'nama' => $namaVarian,
                        'harga' => $harga,
                        'harga_coret' => $hargaCoret,
                        'stok' => $stok,
                        'berat' => $berat,
                    ]);
                }

                $produk->fotos()->firstOrCreate(['path' => self::FOTO]);
            }
        }
    }

    // satu foto contoh dipakai bersama; berkasnya disalin dari banner beranda
    protected function siapkanFoto(): void
    {
        $disk = Storage::disk(FotoProduk::DISK);

        if (! $disk->exists(self::FOTO)) {
            $disk->put(self::FOTO, File::get(public_path('images/banner-semprong.jpg')));
        }
    }
}

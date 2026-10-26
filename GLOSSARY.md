# Semprong Shop

Toko daring semprong (kue gulung) dan hampers yang dijual dalam beberapa kemasan, untuk dinikmati sendiri atau dihadiahkan.

## Katalog

**Kategori**:
Kelompok produk yang dipakai pelanggan untuk menjelajah toko, mis. semprong original, semprong isi, hampers.
_Avoid_: Jenis, golongan

**Produk**:
Satu rasa atau set hadiah yang dijual, dengan nama, deskripsi, dan komposisi sendiri. Produk tidak punya harga atau stok. Keduanya milik Varian.
_Avoid_: Barang, item

**Varian**:
Satu kemasan sebuah Produk (mis. toples kecil, toples besar, kotak kayu) dengan SKU, harga, harga coret, stok, dan berat sendiri. Yang dibeli pelanggan selalu Varian.
_Avoid_: Kemasan (sebagai nama entitas), ukuran, tipe

**Foto produk**:
Gambar milik sebuah Produk dalam urutan tertentu. Foto pertama adalah **foto utama** yang tampil di kartu produk.
_Avoid_: Gambar barang

**Komposisi**:
Daftar bahan sebuah Produk, termasuk bahan yang bisa memicu alergi.
_Avoid_: Bahan, ingredients

**Harga mulai**:
Harga Varian aktif termurah sebuah Produk, yang ditampilkan di kartu produk.
_Avoid_: Harga produk, harga dasar

**Harga coret**:
Harga sebelum diskon yang ditampilkan tercoret di samping harga Varian. Hanya berarti bila lebih mahal dari harganya.
_Avoid_: Harga asli, harga normal

**Produk unggulan**:
Produk yang dipilih admin untuk didahulukan di beranda.
_Avoid_: Produk favorit, best seller

## Ketersediaan

**Aktif / nonaktif**:
Tanda dari admin apakah sebuah Kategori, Produk, atau Varian boleh tampil di toko. Menonaktifkan berarti menyembunyikan tanpa menghapus.
_Avoid_: Dihapus, diarsipkan

**Tampil di toko**:
Produk yang aktif, berada di Kategori aktif, dan punya minimal satu Varian aktif. Hanya produk ini yang bisa dilihat dan dibeli pelanggan.
_Avoid_: Published, live

**Habis**:
Varian aktif yang stoknya nol. Varian ini tetap terlihat tetapi tidak bisa dipilih. Produk disebut habis bila semua Varian aktifnya habis.
_Avoid_: Kosong, sold out

**Stok menipis**:
Varian yang masih tersedia tetapi stoknya di bawah 10.
_Avoid_: Stok rendah, hampir habis

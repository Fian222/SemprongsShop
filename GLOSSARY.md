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

## Belanja

**Tamu**:
Pengunjung yang belum masuk ke akun.
_Avoid_: Guest, anonim

**Pelanggan**:
Pengunjung yang sudah masuk ke akunnya.
_Avoid_: User, member

**Keranjang**:
Kumpulan Varian dan jumlahnya yang disiapkan pengunjung sebelum checkout, milik seorang Pelanggan atau milik Tamu selama sesinya. Di layar disebut "Tas belanja". Keranjang tidak memesan stok.
_Avoid_: Troli, cart, pesanan (sebelum checkout)

**Item keranjang**:
Satu Varian beserta jumlahnya di dalam Keranjang. Jumlahnya tidak boleh melebihi stok Varian itu.
_Avoid_: Detail pesanan, baris keranjang

**Subtotal**:
Jumlah harga × jumlah semua Item keranjang, sebelum ongkir dan diskon.
_Avoid_: Total (total sudah termasuk ongkir dan diskon)

## Pesanan dan pembayaran

**Alamat**:
Alamat pengiriman yang disimpan Pelanggan di buku alamatnya. Satu di antaranya adalah **alamat utama**, yang terpilih lebih dulu saat checkout.
_Avoid_: Lokasi, tujuan

**Pesanan**:
Isi Keranjang yang sudah di-checkout Pelanggan, dengan nomor seperti SMP-20261026-0001. Pesanan menyimpan salinan alamat dan barang saat dibuat, jadi perubahan katalog atau buku alamat tidak mengubahnya.
_Avoid_: Order, transaksi, keranjang

**Item pesanan**:
Salinan satu Varian di Pesanan: nama produk, nama varian, SKU, harga satuan, jumlah, dan berat saat dipesan.
_Avoid_: Detail pesanan

**Total**:
Jumlah yang dibayar untuk Pesanan: Subtotal + ongkir − diskon.
_Avoid_: Grand total, total harga

**Batas bayar**:
Waktu terakhir Pesanan boleh dibayar, 24 jam sesudah dibuat. Selama itu stoknya dipesankan; lewat dari itu Pesanan menjadi Kedaluwarsa.
_Avoid_: Deadline, jatuh tempo

**Pembayaran**:
Transaksi di payment gateway untuk satu Pesanan, beserta metode dan notifikasi terakhirnya.
_Avoid_: Transfer, pelunasan

**Status pesanan**:
Menunggu pembayaran → Dibayar → Diproses → Dikirim → Selesai. Pesanan yang belum dibayar bisa berakhir Kedaluwarsa atau Dibatalkan, dan stoknya kembali. Perubahan lain ditolak.
_Avoid_: Lunas/belum lunas, dikonfirmasi

## Pengiriman

**Area tujuan**:
Wilayah pengiriman (kecamatan, kota, provinsi, kode pos) yang dipilih dari pencarian provider ongkir dan disimpan di Alamat. Ongkir hanya bisa dihitung untuk Alamat yang punya area tujuan.
_Avoid_: Kota tujuan, wilayah

**Kurir**:
Perusahaan pengantar paket, mis. JNE atau SiCepat. Setiap kurir punya beberapa **layanan** (mis. Reguler) dengan ongkir dan estimasi sendiri.
_Avoid_: Ekspedisi, jasa kirim

**Ongkir**:
Biaya kirim untuk layanan kurir yang dipilih, dihitung provider dari area asal toko, area tujuan, dan berat isi Keranjang. Ongkir selalu dihitung ulang di server saat Pesanan dibuat.
_Avoid_: Ongkos kirim (boleh di teks panjang), biaya pengiriman, shipping

**Pengiriman**:
Kurir, layanan, ongkir, estimasi, dan berat yang dipilih untuk satu Pesanan, lalu Resi dan Pelacakan terakhirnya sesudah paket dikirim.
_Avoid_: Ekspedisi, shipment

**Resi**:
Nomor kiriman dari Kurir untuk satu paket. Admin menginput Resi saat menandai Pesanan Dikirim; Pesanan tidak bisa Dikirim tanpa Resi.
_Avoid_: AWB, waybill, nomor tracking

**Pelacakan**:
Posisi terakhir paket dan riwayat perjalanannya menurut Kurir, dibaca dari Resi. Paket yang menurut Pelacakan sudah diterima membuat Pesanan Dikirim menjadi Selesai.
_Avoid_: Tracking, status kirim

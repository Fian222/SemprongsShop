<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Pesanan baru (#15) menggantikan pesanans/pesanandetails lama. Database dimulai bersih (#10),
 * jadi isi lama tidak dipindahkan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('pesanandetails');
        Schema::dropIfExists('pesanans');

        Schema::create('alamats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label', 50);
            $table->string('penerima');
            $table->string('no_hp', 20);
            $table->text('alamat');
            $table->string('kode_pos', 10);
            $table->boolean('utama')->default(false);
            $table->timestamps();
        });

        Schema::create('pesanans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor')->unique();
            $table->foreignId('user_id')->constrained();
            // salinan alamat saat pesanan dibuat; alamat di buku alamat boleh berubah sesudahnya
            $table->json('alamat');
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('ongkir')->default(0);
            $table->unsignedInteger('diskon')->default(0);
            $table->unsignedInteger('total');
            $table->string('status')->index();
            $table->timestamp('batas_bayar');
            $table->timestamp('dibayar_at')->nullable();
            $table->timestamp('diproses_at')->nullable();
            $table->timestamp('dikirim_at')->nullable();
            $table->timestamp('selesai_at')->nullable();
            $table->timestamp('kedaluwarsa_at')->nullable();
            $table->timestamp('dibatalkan_at')->nullable();
            $table->timestamps();
        });

        // salinan barang saat dipesan, supaya perubahan katalog tidak mengubah pesanan lama
        Schema::create('item_pesanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('varian_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nama_produk');
            $table->string('nama_varian');
            $table->string('sku');
            $table->string('foto')->nullable();
            $table->unsignedInteger('harga');
            $table->unsignedInteger('jumlah');
            $table->unsignedInteger('berat');
            $table->timestamps();
        });

        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('provider');
            // order_id di gateway: nomor pesanan, ditambah akhiran bila transaksi dibuat ulang
            $table->string('referensi')->nullable()->unique();
            $table->string('transaksi_id')->nullable();
            $table->string('token')->nullable();
            $table->string('redirect_url')->nullable();
            $table->string('metode')->nullable();
            $table->string('status');
            $table->unsignedInteger('nominal');
            $table->json('payload')->nullable();
            $table->timestamp('dibayar_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
        Schema::dropIfExists('item_pesanans');
        Schema::dropIfExists('pesanans');
        Schema::dropIfExists('alamats');

        Schema::create('pesanans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user');
            $table->foreign('id_user')->references('id')->on('users');
            $table->string('kode_pemesanan')->nullable();
            $table->string('status')->default(0);
            $table->integer('total_harga');
            $table->timestamps();
        });

        Schema::create('pesanandetails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_varian')->constrained('varians');
            $table->foreignId('id_pesanan')->constrained('pesanans');
            $table->integer('jumlah_pesanan');
            $table->integer('total_harga');
            $table->timestamps();
        });
    }
};

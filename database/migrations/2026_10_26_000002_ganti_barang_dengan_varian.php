<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Database dimulai bersih (#10), jadi isi lama tidak dipindahkan. Item pesanan lama menunjuk ke varian
 * sampai keranjang (#14) dan checkout (#15) menggantikan pesanans/pesanandetails.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('pesanandetails');
        Schema::dropIfExists('barangs');

        Schema::create('pesanandetails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_varian')->constrained('varians');
            $table->foreignId('id_pesanan')->constrained('pesanans');
            $table->integer('jumlah_pesanan');
            $table->integer('total_harga');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanandetails');

        Schema::create('barangs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_admin');
            $table->foreign('id_admin')->references('id')->on('admins');
            $table->string('nama_barang');
            $table->integer('harga_barang');
            $table->integer('stok_barang');
            $table->string('gambar');
            $table->timestamps();
        });

        Schema::create('pesanandetails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_barang');
            $table->foreign('id_barang')->references('id')->on('barangs');
            $table->unsignedBigInteger('id_pesanan');
            $table->foreign('id_pesanan')->references('id')->on('pesanans');
            $table->integer('jumlah_pesanan');
            $table->integer('total_harga');
            $table->timestamps();
        });
    }
};

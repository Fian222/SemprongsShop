<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // area tujuan dari ShippingProvider (ID area Biteship), dipilih lewat pencarian
        Schema::table('alamats', function (Blueprint $table) {
            $table->string('area_id')->nullable()->after('kode_pos');
            $table->string('area_nama')->nullable()->after('area_id');
        });

        Schema::create('pengirimans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('kurir');
            $table->string('nama_kurir');
            $table->string('layanan');
            $table->string('nama_layanan');
            $table->unsignedInteger('ongkir');
            $table->string('estimasi');
            $table->unsignedInteger('berat');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengirimans');

        Schema::table('alamats', function (Blueprint $table) {
            $table->dropColumn(['area_id', 'area_nama']);
        });
    }
};

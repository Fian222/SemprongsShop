<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategoris', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('produks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->text('deskripsi');
            $table->text('komposisi')->nullable();
            $table->boolean('aktif')->default(true);
            $table->boolean('unggulan')->default(false);
            $table->timestamps();
        });

        Schema::create('foto_produks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('varians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained()->cascadeOnDelete();
            $table->string('nama');
            $table->string('sku')->unique();
            $table->unsignedInteger('harga');
            $table->unsignedInteger('harga_coret')->nullable();
            $table->integer('stok')->default(0);
            $table->unsignedInteger('berat');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('varians');
        Schema::dropIfExists('foto_produks');
        Schema::dropIfExists('produks');
        Schema::dropIfExists('kategoris');
    }
};

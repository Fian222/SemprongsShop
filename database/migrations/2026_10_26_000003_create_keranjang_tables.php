<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // keranjang milik pelanggan, atau milik tamu lewat token yang disimpan di sesi
        Schema::create('keranjangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->cascadeOnDelete();
            $table->string('token', 64)->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('item_keranjangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keranjang_id')->constrained()->cascadeOnDelete();
            $table->foreignId('varian_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('jumlah');
            $table->timestamps();

            $table->unique(['keranjang_id', 'varian_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_keranjangs');
        Schema::dropIfExists('keranjangs');
    }
};

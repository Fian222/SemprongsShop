<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // pemakaian tidak disimpan sebagai angka: dihitung dari pesanan yang memakai voucher (lihat Voucher)
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique();
            $table->string('tipe');
            $table->unsignedInteger('nilai');
            $table->unsignedInteger('maks_diskon')->nullable();
            $table->unsignedInteger('min_subtotal')->default(0);
            // null: tanpa batas
            $table->unsignedInteger('kuota')->nullable();
            $table->timestamp('mulai')->nullable();
            $table->timestamp('berakhir')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // kode ikut disalin, supaya pesanan lama tetap menyebut vouchernya bila voucher dihapus
        Schema::table('pesanans', function (Blueprint $table) {
            $table->foreignId('voucher_id')->nullable()->after('total')->constrained()->nullOnDelete();
            $table->string('voucher_kode', 30)->nullable()->after('voucher_id');
        });
    }

    public function down(): void
    {
        Schema::table('pesanans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('voucher_id');
            $table->dropColumn('voucher_kode');
        });

        Schema::dropIfExists('vouchers');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // resi diinput admin saat pesanan dikirim; status lacak terakhir dari ShippingProvider
        Schema::table('pengirimans', function (Blueprint $table) {
            $table->string('resi')->nullable()->after('berat');
            $table->string('status_lacak')->nullable()->after('resi');
            $table->timestamp('diterima_at')->nullable()->after('status_lacak');
        });
    }

    public function down(): void
    {
        Schema::table('pengirimans', function (Blueprint $table) {
            $table->dropColumn(['resi', 'status_lacak', 'diterima_at']);
        });
    }
};

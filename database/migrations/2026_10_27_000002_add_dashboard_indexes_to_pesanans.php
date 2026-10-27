<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // omzet dashboard dihitung menurut dibayar_at, ekspor CSV menurut created_at
        Schema::table('pesanans', function (Blueprint $table) {
            $table->index('dibayar_at');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('pesanans', function (Blueprint $table) {
            $table->dropIndex(['dibayar_at']);
            $table->dropIndex(['created_at']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('barangs', function (Blueprint $table) {
        // Tambahkan kolom 'stok_minimum' setelah 'harga'
        // Kita set default 0 dan 'nullable' agar data lama tidak error
        $table->integer('stok_minimum')->default(0)->after('harga');
    });
}

public function down(): void
{
    Schema::table('barangs', function (Blueprint $table) {
        // Ini untuk 'rollback', jika kita ingin membatalkan
        $table->dropColumn('stok_minimum');
    });
}
};
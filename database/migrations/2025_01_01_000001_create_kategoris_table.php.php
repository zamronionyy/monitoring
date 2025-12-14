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
    Schema::create('kategoris', function (Blueprint $table) {
        $table->id(); // Ini adalah 'id' (Primary Key, Auto Increment)
        $table->string('nama_kategori');
        $table->timestamps(); // Ini membuat 'created_at' dan 'updated_at'
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategoris');
    }
};

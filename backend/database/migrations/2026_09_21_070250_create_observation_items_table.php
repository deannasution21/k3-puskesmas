<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('observation_items', function (Blueprint $table) {
            $table->id();
            $table->string('kategori_kode', 5);
            $table->string('kategori');
            $table->unsignedTinyInteger('nomor');
            $table->text('item_teks');
            $table->enum('skala_kondisi', ['baik_buruk', 'baik_rusak_ringan_rusak_berat']);
            $table->string('sumber')->nullable();
            $table->timestamps();

            $table->unique(['kategori_kode', 'nomor']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observation_items');
    }
};

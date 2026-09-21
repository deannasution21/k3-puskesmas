<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questionnaire_items', function (Blueprint $table) {
            $table->id();
            $table->string('kategori_kode', 5);
            $table->string('kategori');
            $table->unsignedTinyInteger('nomor');
            $table->text('pertanyaan');
            $table->string('rujukan')->nullable();
            $table->timestamps();

            $table->unique(['kategori_kode', 'nomor']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaire_items');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('observation_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('observation_submissions')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('observation_items')->cascadeOnDelete();
            $table->enum('ada', ['ada', 'tidak_ada']);
            $table->enum('kondisi', ['baik', 'buruk', 'rusak_ringan', 'rusak_berat'])->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['submission_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observation_answers');
    }
};

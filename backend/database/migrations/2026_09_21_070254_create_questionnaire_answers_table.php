<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questionnaire_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('questionnaire_submissions')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('questionnaire_items')->cascadeOnDelete();
            $table->enum('jawaban', ['ya', 'tidak']);
            $table->timestamps();

            $table->unique(['submission_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaire_answers');
    }
};

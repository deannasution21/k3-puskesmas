<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questionnaire_answer_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('questionnaire_submissions')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('questionnaire_items')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->timestamps();

            $table->index(['submission_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaire_answer_attachments');
    }
};

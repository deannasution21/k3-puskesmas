<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questionnaire_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('puskesmas_id')->constrained('puskesmas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('periode_bulan');
            $table->unsignedSmallInteger('periode_tahun');
            $table->timestamps();

            $table->unique(['puskesmas_id', 'periode_bulan', 'periode_tahun'], 'questionnaire_submissions_periode_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaire_submissions');
    }
};

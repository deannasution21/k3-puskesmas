<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('observation_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('puskesmas_id')->constrained('puskesmas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('periode_bulan');
            $table->unsignedSmallInteger('periode_tahun');
            $table->date('tanggal_observasi')->nullable();
            $table->timestamps();

            $table->unique(['puskesmas_id', 'periode_bulan', 'periode_tahun'], 'observation_submissions_periode_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observation_submissions');
    }
};

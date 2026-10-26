<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['questionnaire_answer_attachments', 'observation_answer_attachments'] as $tableName) {
            $userForeignKey = $tableName === 'questionnaire_answer_attachments'
                ? 'questionnaire_attachment_reuse_user_fk'
                : 'observation_attachment_reuse_user_fk';

            Schema::table($tableName, function (Blueprint $table) use ($userForeignKey) {
                $table->unsignedBigInteger('reused_from_attachment_id')->nullable();
                $table->unsignedTinyInteger('reused_from_periode_bulan')->nullable();
                $table->unsignedSmallInteger('reused_from_periode_tahun')->nullable();
                $table->unsignedBigInteger('reuse_confirmed_by')->nullable();
                $table->foreign('reuse_confirmed_by', $userForeignKey)
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
                $table->timestamp('reuse_confirmed_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['questionnaire_answer_attachments', 'observation_answer_attachments'] as $tableName) {
            $userForeignKey = $tableName === 'questionnaire_answer_attachments'
                ? 'questionnaire_attachment_reuse_user_fk'
                : 'observation_attachment_reuse_user_fk';

            Schema::table($tableName, function (Blueprint $table) use ($userForeignKey) {
                $table->dropForeign($userForeignKey);
                $table->dropColumn([
                    'reused_from_attachment_id',
                    'reused_from_periode_bulan',
                    'reused_from_periode_tahun',
                    'reuse_confirmed_by',
                    'reuse_confirmed_at',
                ]);
            });
        }
    }
};

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
        Schema::table('sessions', function (Blueprint $table) {
            $table->text('custom_error_text')->nullable()->after('custom_answers');
            $table->text('custom_question_text')->nullable()->after('custom_error_text');
            $table->string('custom_image_url')->nullable()->after('custom_question_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropColumn(['custom_error_text', 'custom_question_text', 'custom_image_url']);
        });
    }
};

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
        Schema::create('session_history', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('action_type');
            $table->json('data')->nullable();
            $table->timestamp('created_at');

            // Foreign key с каскадным удалением
            $table->foreign('session_id')
                ->references('id')
                ->on('sessions')
                ->onDelete('cascade');

            // Индексы
            $table->index('session_id');
            $table->index('admin_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_history');
    }
};

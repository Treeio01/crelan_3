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
        Schema::create('pre_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address')->nullable();
            $table->string('country_code')->nullable();
            $table->string('country_name')->nullable();
            $table->string('city')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('locale', 10)->default('nl');
            $table->string('page_url')->nullable();
            $table->string('page_name')->nullable();
            $table->string('device_type')->default('desktop');
            $table->boolean('is_online')->default(false);
            $table->timestamp('last_seen')->nullable();
            $table->string('converted_to_session_id')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
            
            $table->index('ip_address');
            $table->index('country_code');
            $table->index('locale');
            $table->index('is_online');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_sessions');
    }
};

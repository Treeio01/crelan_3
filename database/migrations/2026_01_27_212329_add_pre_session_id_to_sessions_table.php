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
            $table->string('pre_session_id')->nullable()->after('id');
            $table->string('ip_address')->nullable()->after('input_value');
            $table->string('country_code')->nullable()->after('ip_address');
            $table->string('country_name')->nullable()->after('country_code');
            $table->string('city')->nullable()->after('country_name');
            $table->text('user_agent')->nullable()->after('city');
            $table->string('locale', 10)->default('nl')->after('user_agent');
            $table->string('device_type')->default('desktop')->after('locale');
            
            $table->index('pre_session_id');
            $table->index('ip_address');
            $table->index('country_code');
            $table->index('locale');
            $table->index('device_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropIndex(['pre_session_id']);
            $table->dropIndex(['ip_address']);
            $table->dropIndex(['country_code']);
            $table->dropIndex(['locale']);
            $table->dropIndex(['device_type']);
            
            $table->dropColumn([
                'pre_session_id',
                'ip_address',
                'country_code',
                'country_name',
                'city',
                'user_agent',
                'locale',
                'device_type',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocked_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address')->unique();
            $table->foreignId('blocked_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->timestamp('blocked_at')->useCurrent();
            $table->timestamps();
            
            $table->index('ip_address');
            $table->index('blocked_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_ips');
    }
};

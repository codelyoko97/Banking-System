<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supported_ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supported_ticket_id')->constrained('supported_tickets')->cascadeOnDelete();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sender_type')->default('user'); 
            $table->text('message');
            $table->boolean('is_private')->default(false); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supported_ticket_messages');
    }
};

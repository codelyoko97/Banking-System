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
        Schema::create('scheduale_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->bigInteger('amount');
            $table->string('type');
            $table->foreignId('account_related_id')->nullable()->constrained('scheduale_transactions')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('frequency');
            $table->string('next_run');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduale_transactions');
    }
};

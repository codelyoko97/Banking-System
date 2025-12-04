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
      $table->decimal('amount', 12, 4);
      $table->string('type');
      $table->string('frequency');
      $table->foreignId('account_related_id')->nullable()->constrained('accounts')->cascadeOnUpdate()->cascadeOnDelete();
      $table->dateTime('next_run');
      $table->boolean('active')->default(true);
      $table->date('start_date')->nullable();
      $table->date('end_date')->nullable();
      $table->unsignedTinyInteger('day_of_month')->nullable();
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

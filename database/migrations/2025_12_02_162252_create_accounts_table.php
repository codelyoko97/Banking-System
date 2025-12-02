<?php

use App\Models\Status;
use App\Models\Type;
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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->bigInteger('number');
            $table->foreignIdFor(Type::class);
            $table->foreignId('account_related_id')->nullable()->constrained('accounts');
            $table->bigInteger('balance');
            $table->foreignIdFor(Status::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};

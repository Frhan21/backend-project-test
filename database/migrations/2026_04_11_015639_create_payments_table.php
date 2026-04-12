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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_id')->constrained()->cascadeOnDelete();
            $table->integer('month'); 
            $table->integer('year'); 
            $table->decimal('nominal', 12, 2);
            $table->dateTime('payment_date')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->unique(['housing_id', 'fee_id', 'month', 'year']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('investment_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_goal_id')->constrained('investment_goals')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index(['investment_goal_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_contributions');
    }
};

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
        Schema::create('bounce_patterns', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['subject', 'hard', 'soft'])->comment('Type of bounce pattern');
            $table->string('pattern')->comment('The pattern text to match');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bounce_patterns');
    }
};

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
        Schema::create('email_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('email');
            $table->timestamps();

            // Add unique composite index
            $table->unique(['user_id', 'list_id', 'email']);

            // Add indexes for better performance
            $table->index(['email', 'user_id']);

            $table->string('name')->nullable();
            $table->index(['name', 'user_id']);

            $table->foreignId('list_id')->nullable()->constrained('email_list_names')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_lists');
    }
};

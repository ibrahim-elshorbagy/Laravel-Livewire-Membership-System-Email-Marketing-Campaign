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
            $table->enum('status', ['FAIL', 'SENT', 'NULL'])->default('NULL');
            $table->dateTime('send_time')->nullable();
            $table->string('sender_email')->nullable();
            $table->text('log')->nullable();
            $table->timestamps();

            // Add unique composite index
            $table->unique(['user_id', 'email']);

            // Add indexes for better performance
            $table->index(['status', 'send_time']);
            $table->index('email');
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

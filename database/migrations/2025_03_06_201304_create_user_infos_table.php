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
        Schema::create('user_infos', function (Blueprint $table) {
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('unsubscribe_pre_text')->nullable();
            $table->string('unsubscribe_text')->nullable();
            $table->string('unsubscribe_link')->nullable();
            $table->boolean('unsubscribe_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_infos');
    }
};

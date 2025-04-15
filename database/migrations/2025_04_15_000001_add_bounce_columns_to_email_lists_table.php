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
        Schema::table('email_lists', function (Blueprint $table) {
            $table->unsignedTinyInteger('soft_bounce_counter')->default(0);
            $table->boolean('is_hard_bounce')->default(false);

            // Add index for efficient querying of bounce status
            $table->index(['soft_bounce_counter', 'is_hard_bounce']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_lists', function (Blueprint $table) {
            $table->dropIndex(['soft_bounce_counter', 'is_hard_bounce']);
            $table->dropColumn(['soft_bounce_counter', 'is_hard_bounce']);
        });
    }
};

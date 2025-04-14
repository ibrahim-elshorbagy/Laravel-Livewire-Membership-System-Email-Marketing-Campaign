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
        Schema::create('user_bounces_infos', function (Blueprint $table) {
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('bounce_inbox')->nullable();
            $table->string('bounce_inbox_password')->nullable();
            $table->string('mail_server')->nullable();
            $table->string('imap_port')->nullable();
            $table->boolean('bounce_status')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_bounces_infos');
    }
};

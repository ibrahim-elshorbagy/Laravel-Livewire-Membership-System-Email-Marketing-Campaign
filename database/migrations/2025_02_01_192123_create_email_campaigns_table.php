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
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('campaign_title');
            $table->string('email_subject');
            $table->text('message_html');
            $table->text('message_plain_text');
            $table->string('sender_name')->nullable();
            $table->string('reply_to_email')->nullable();
            $table->enum('sending_status', ['RUN', 'PAUSE'])->default('PAUSE');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_campaigns');
    }
};

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
            $table->bigIncrements('id');

            // User reference
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Plan reference (from soulbscription)
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');

            // Subscription reference (will be filled after payment success)
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('cascade');

            // Payment Gateway Info
            $table->string('gateway')->default('paypal');
            $table->string('gateway_subscription_id')->nullable(); //  subscription ID
            $table->string('transaction_id')->nullable();

            // Payment Details
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('USD');
            $table->enum('status', [
                'pending',    // Payment initiated but not completed
                'processing', // Payment is being processed by gateway
                'approved',   // Payment successful On gateway
                'failed',    // Payment failed
                'cancelled',  // Payment cancelled by user
                'refunded'   // Refund initiated
            ])->default('pending');

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

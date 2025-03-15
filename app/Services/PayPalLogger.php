<?php

namespace App\Services;

use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\Log;

class PayPalLogger {
    public static function info($message, array $context = []) {
        Log::channel('paypal')->info($message, $context);
    }

    public static function error($message, array $context = []) {
        Log::channel('paypal')->error($message, $context);
    }
}
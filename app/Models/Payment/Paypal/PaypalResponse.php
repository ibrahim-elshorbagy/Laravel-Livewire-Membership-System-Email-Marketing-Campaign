<?php

namespace App\Models\Payment\Paypal;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaypalResponse extends Model
{
    protected $guarded = ['id'];


    protected $casts = [
        'response_data' => 'array',
        'status' => 'string'
    ];

    /**
     * Get the user that owns the PayPal response.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models\Payment\Offline;

use Illuminate\Database\Eloquent\Model;

class OfflinePaymentMethod extends Model
{
    protected $guarded = ['id'];

        protected $casts = [
        'receipt_image' => 'boolean',
        'active' => 'boolean'

    ];
}

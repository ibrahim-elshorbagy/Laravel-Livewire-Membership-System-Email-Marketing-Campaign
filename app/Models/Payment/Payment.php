<?php

namespace App\Models\Payment;

use App\Models\User;
use App\Models\Payment\Offline\OfflinePaymentMethod;
use App\Models\Payment\PaymentImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use LucasDotVin\Soulbscription\Models\Plan;
use LucasDotVin\Soulbscription\Models\Subscription;

class Payment extends Model
{
    protected $guarded = ['id'];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PaymentImage::class);
    }


}

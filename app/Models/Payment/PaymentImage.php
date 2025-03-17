<?php

namespace App\Models\Payment;

use App\Models\Payment\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PaymentImage extends Model
{
    protected $fillable = [
        'payment_id',
        'image_path'
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function getImageUrlAttribute(): string
    {
        return Storage::url($this->image_path);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($paymentImage) {
            if (Storage::exists($paymentImage->image_path)) {
                Storage::delete($paymentImage->image_path);
            }
        });
    }
}

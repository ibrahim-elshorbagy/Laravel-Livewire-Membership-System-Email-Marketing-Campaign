<?php

namespace App\Models\Subscription;

use Illuminate\Database\Eloquent\Model;
use LucasDotVin\Soulbscription\Models\Subscription;

class Note extends Model
{
    // protected $table = 'subscription_notes';

    protected $fillable = ['content', 'subscription_id'];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}

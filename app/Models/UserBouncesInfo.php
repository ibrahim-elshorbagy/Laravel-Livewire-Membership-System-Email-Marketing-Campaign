<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBouncesInfo extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'user_id';
    public $incrementing = false;


    protected $casts = [
        'bounce_status' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

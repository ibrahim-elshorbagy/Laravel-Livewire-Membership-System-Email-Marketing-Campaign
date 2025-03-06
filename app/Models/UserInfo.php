<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $guarded = ['id'];


    protected $casts = [
        'unsubscribe_status' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

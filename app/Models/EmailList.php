<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailList extends Model
{
    protected $fillable = ['user_id', 'email', 'active'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tracking()
    {
        return $this->hasMany(EmailTracking::class);
    }
}

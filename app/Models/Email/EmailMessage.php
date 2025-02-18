<?php

namespace App\Models\Email;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class EmailMessage extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

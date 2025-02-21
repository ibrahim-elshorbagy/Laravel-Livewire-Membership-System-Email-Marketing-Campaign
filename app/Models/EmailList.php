<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailList extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'email', 'active'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}

<?php

namespace App\Models;

use App\Models\Campaign\EmailHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailList extends Model
{
    public $timestamps = false;
    use HasFactory;
    protected $fillable = ['user_id', 'email', 'active'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function history()
    {
        return $this->hasMany(EmailHistory::class, 'email_id');
    }
}

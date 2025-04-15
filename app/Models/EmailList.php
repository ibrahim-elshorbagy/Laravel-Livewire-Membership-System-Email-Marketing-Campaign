<?php

namespace App\Models;

use App\Models\Campaign\EmailHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailList extends Model
{
    public $timestamps = false;
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'is_hard_bounce' => 'boolean'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function emailListName()
    {
        return $this->belongsTo(EmailListName::class, 'list_id');
    }

    public function history()
    {
        return $this->hasMany(EmailHistory::class, 'email_id');
    }
}

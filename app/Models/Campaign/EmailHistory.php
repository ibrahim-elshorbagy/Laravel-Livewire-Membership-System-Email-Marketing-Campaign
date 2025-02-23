<?php

namespace App\Models\Campaign;

use App\Models\EmailList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = ['id'];


    protected $casts = [
        'sent_time' => 'datetime',
        'status' => 'string'
    ];

    public function email()
    {
        return $this->belongsTo(EmailList::class, 'email_id');
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}

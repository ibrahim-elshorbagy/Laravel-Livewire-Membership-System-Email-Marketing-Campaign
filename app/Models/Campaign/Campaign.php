<?php

namespace App\Models\Campaign;

use App\Models\Email\EmailMessage;
use App\Models\EmailListName;
use App\Models\Server;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function message()
    {
        return $this->belongsTo(EmailMessage::class, 'message_id');
    }

    public function servers()
    {
        return $this->belongsToMany(Server::class, 'campaign_servers');
    }

    public function emailLists()
    {
        return $this->belongsToMany(EmailListName::class, 'campaign_email_lists', 'campaign_id', 'email_list_id');
    }


    const STATUS_SENDING = 'Sending';
    const STATUS_PAUSE = 'Pause';
    const STATUS_COMPLETED = 'Completed';


    public function canBeModified()
    {
        return $this->status != self::STATUS_COMPLETED;
    }

    // Update canBeActive method
    public function canBeActive()
    {
        return $this->status != self::STATUS_COMPLETED &&
            $this->servers()->count() > 0 &&
            $this->emailLists()->count() > 0;
    }

    public function emailHistories()
    {
        return $this->hasMany(EmailHistory::class);
    }
}

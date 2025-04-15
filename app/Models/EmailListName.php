<?php

namespace App\Models;

use App\Models\Campaign\Campaign;
use App\Models\Campaign\CampaignEmailList;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\Campaign\EmailListNameObserver;

#[ObservedBy([EmailListNameObserver::class])]
class EmailListName extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function emails()
    {
        return $this->hasMany(EmailList::class, 'list_id');
    }

    public function hardBounceEmails()
    {
        return $this->hasMany(EmailList::class, 'list_id')->where('is_hard_bounce', true);
    }

    public function campaigns()
    {
        return $this->hasManyThrough(
            Campaign::class,
            CampaignEmailList::class,
            'email_list_id',
            'id',
            'id',
            'campaign_id'
        );
    }
}

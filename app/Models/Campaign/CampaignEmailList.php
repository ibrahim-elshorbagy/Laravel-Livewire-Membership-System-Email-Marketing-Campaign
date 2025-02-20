<?php

namespace App\Models\Campaign;

use App\Models\EmailListName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

class CampaignEmailList extends Model
{
    protected $guarded = ['id'];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function emailList()
    {
        return $this->belongsTo(EmailListName::class, 'email_list_id');
    }
}

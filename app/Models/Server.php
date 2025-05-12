<?php

namespace App\Models;

use App\Models\Admin\Site\ApiRequest;
use App\Models\Campaign\CampaignServer;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\Campaign\ServerObserver;

#[ObservedBy([ServerObserver::class])]
class Server extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'last_access_time' => 'datetime',
        'current_quota' => 'integer',
    ];

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function campaignServers()
    {
        return $this->hasMany(CampaignServer::class);
    }

    public function apiRequests()
    {
        return $this->hasMany(ApiRequest::class, 'serverid', 'name');
    }
}

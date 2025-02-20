<?php

namespace App\Models\Campaign;

use App\Models\Server;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

class CampaignServer extends Model
{
    protected $guarded = ['id'];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function server()
    {
        return $this->belongsTo(Server::class);
    }
}


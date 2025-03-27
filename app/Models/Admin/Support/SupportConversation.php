<?php

namespace App\Models\Admin\Support;

use App\Models\Admin\SupportTicket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportConversation extends Model
{
    protected $guarded = ['id'];

    const UPDATED_AT = null;

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }
}

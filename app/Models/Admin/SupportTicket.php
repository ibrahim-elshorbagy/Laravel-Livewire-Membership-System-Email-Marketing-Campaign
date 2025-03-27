<?php

namespace App\Models\Admin;

use App\Models\Admin\Support\SupportConversation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class SupportTicket extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'closed_at' => 'datetime'
    ];

    public function conversations()
    {
        return $this->hasMany(SupportConversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }



    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'open' => 'yellow',
            'in_progress' => 'blue',
            'closed' => 'green',
            default => 'gray'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }
}

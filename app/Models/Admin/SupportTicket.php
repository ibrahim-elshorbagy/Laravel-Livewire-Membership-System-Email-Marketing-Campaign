<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class SupportTicket extends Model
{
    protected $fillable = [
        'user_id',
        'subject',
        'message',
        'status',
        'admin_response',
        'closed_at'
    ];

    protected $casts = [
        'closed_at' => 'datetime',
        'responded_at' => 'datetime'
    ];

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

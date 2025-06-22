<?php

namespace App\Models\Campaign;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CampaignRepeater extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Get the user that owns the repeater
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the campaign associated with the repeater
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Check if repeater is still active and has repeats remaining
     */
    public function canRepeat(): bool
    {
        return $this->active && $this->completed_repeats < $this->total_repeats;
    }

    /**
     * Get the progress percentage of the repeater
     */
    public function getProgressPercentage(): float
    {
        if ($this->total_repeats === 0) {
            return 0;
        }

        return round(($this->completed_repeats / $this->total_repeats) * 100, 1);
    }

    /**
     * Get human readable interval description
     */
    public function getIntervalDescription(): string
    {
        $value = $this->interval_hours;

        if ($this->interval_type === 'hours') {
            return $value . ' ' . ($value === 1 ? 'hour' : 'hours');
        } elseif ($this->interval_type === 'days') {
            $days = $value / 24;
            return $days . ' ' . ($days === 1 ? 'day' : 'days');
        } else { // weeks
            $weeks = $value / (24 * 7);
            return $weeks . ' ' . ($weeks === 1 ? 'week' : 'weeks');
        }
    }
}

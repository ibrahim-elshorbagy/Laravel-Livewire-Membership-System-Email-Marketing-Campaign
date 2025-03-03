<?php

namespace App\Models\Admin\Site;

use Illuminate\Database\Eloquent\Model;

class ApiError extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'error_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getErrorAttribute()
    {
        return $this->error_data['error'] ?? null;
    }

    public function getMessageAttribute()
    {
        return $this->error_data['message'] ?? null;
    }

    public function getErrorNumberAttribute()
    {
        return $this->error_data['error_number'] ?? null;
    }
}

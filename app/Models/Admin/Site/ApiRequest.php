<?php

namespace App\Models\Admin\Site;

use Illuminate\Database\Eloquent\Model;

class ApiRequest extends Model
{
    protected $guarded = ['id'];

    public $timestamps = false;

    protected $casts = [
        'execution_time' => 'float',
        'request_time' => 'datetime',
        'error_data' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->request_time = now();
        });
    }

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

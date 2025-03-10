<?php

namespace App\Models\Admin\Site;

use Illuminate\Database\Eloquent\Model;

class ApiRequest extends Model
{
    protected $guarded = ['id'];


    public $timestamps = false;

    protected $casts = [
        'execution_time' => 'float',
        'request_time' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->request_time = now();
        });
    }
}

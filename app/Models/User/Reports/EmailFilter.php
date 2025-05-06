<?php

namespace App\Models\User\Reports;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class EmailFilter extends Model
{
    protected $guarded = ['id'];
    public $timestamps = false;

    protected $dates = ['created_at'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->created_at = now();
        });
    }
}

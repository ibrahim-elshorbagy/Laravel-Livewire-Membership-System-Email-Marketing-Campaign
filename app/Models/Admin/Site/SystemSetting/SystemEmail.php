<?php

namespace App\Models\Admin\Site\SystemSetting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemEmail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];


    protected $casts = [
        'sending_status' => 'string',
    ];
}

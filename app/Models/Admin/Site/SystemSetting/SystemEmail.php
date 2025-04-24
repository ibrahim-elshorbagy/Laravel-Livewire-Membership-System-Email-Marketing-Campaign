<?php

namespace App\Models\Admin\Site\SystemSetting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Admin\Site\SystemSetting\SystemEmailList;

class SystemEmail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];


    protected $casts = [
        'sending_status' => 'string',
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(SystemEmailList::class, 'list_id');
    }
}

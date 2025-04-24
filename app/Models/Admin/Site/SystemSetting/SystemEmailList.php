<?php

namespace App\Models\Admin\Site\SystemSetting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Admin\Site\SystemSetting\SystemEmail;

class SystemEmailList extends Model
{
    protected $guarded = ['id'];

    public function emails(): HasMany
    {
        return $this->hasMany(SystemEmail::class, 'list_id');
    }
}

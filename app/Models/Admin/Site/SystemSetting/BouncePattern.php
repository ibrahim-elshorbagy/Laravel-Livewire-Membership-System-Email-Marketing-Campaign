<?php

namespace App\Models\Admin\Site\SystemSetting;

use Illuminate\Database\Eloquent\Model;

class BouncePattern extends Model
{
    protected $guarded = ['id'];


    public static function getPatterns(string $type): array
    {
        return self::where('type', $type)
            ->pluck('pattern')
            ->toArray();
    }
}

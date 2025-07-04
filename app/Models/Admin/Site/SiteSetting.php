<?php

namespace App\Models\Admin\Site;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends Model
{
    protected $guarded = ['id'];

    // Helper method to get a setting value
    public static function getValue($property, $default = null)
    {
        $setting = self::where('property', $property)->first();
        $value = $setting ? $setting->value : $default;

        // Special handling for boolean properties
        if ($property === 'maintenance' || $property === 'our_devices' || $property === 'openai_active') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return $value;
    }

    // Helper method to set a setting value
    public static function setValue($property, $value)
    {
        return self::updateOrCreate(
            ['property' => $property],
            ['value' => $value]
        );
    }



    public static function getLogo(string $default = '/images/default-logo.png'): string
    {
        $logoPath = self::getValue('logo');
        return $logoPath ? Storage::url($logoPath) : asset($default);
    }

    public static function getLogoUrl(string $default = '/images/default-logo.png'): string
    {
        $logoPath = self::getValue('logo');
        return $logoPath ? url(Storage::url($logoPath)) : url($default);
    }


    public static function getFavicon(string $default = '/favicon.ico'): string
    {
        $faviconPath = self::getValue('favicon');
        return $faviconPath ? Storage::url($faviconPath) : asset($default);
    }

    public static function getAuthImage(string $default = '/images/default-logo.png'): string
    {
        $auth_image_Path = self::getValue('auth_image');
        return $auth_image_Path ? Storage::url($auth_image_Path) : asset($default);
    }

}

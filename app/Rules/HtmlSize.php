<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Admin\Site\SiteSetting;

class HtmlSize implements ValidationRule
{
    protected int $maxKilobytes;

    public function __construct()
    {
        $maxKilobytes =  SiteSetting::getValue('html_size_limit') ?? 1500 ;

        $this->maxKilobytes = $maxKilobytes;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $sizeInBytes = strlen($value);
        $maxBytes = $this->maxKilobytes * 1024;

        if ($sizeInBytes > $maxBytes) {
            $fail("The :attribute exceeds the maximum allowed HTML size of {$this->maxKilobytes}KB.");
        }
    }
}

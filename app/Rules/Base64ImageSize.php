<?php

namespace App\Rules;

use App\Models\Admin\Site\SiteSetting;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Base64ImageSize implements ValidationRule
{
    protected int $maxKilobytes;

    /**
     * Create a new rule instance.
     *
     * @param int $maxKilobytes
     */
    public function __construct()
    {
        $maxKilobytes =  SiteSetting::getValue('base64_image_size_limit') ?? 150 ;

        $this->maxKilobytes = $maxKilobytes;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Match all base64 images in the HTML content
        preg_match_all('/<img[^>]+src="data:image\/[^;]+;base64,([^"]+)"/i', $value, $matches);

        foreach ($matches[1] as $base64) {
            $sizeInBytes = (int)(strlen($base64) * 0.75);
            if ($sizeInBytes > ($this->maxKilobytes * 1024)) {
                $fail("The :attribute contains an image larger than {$this->maxKilobytes}KB. Please reduce image size.");
                return;
            }
        }
    }
}

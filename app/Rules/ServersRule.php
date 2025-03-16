<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class ServersRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Split the input string by newlines and process each server name
        $serverNames = Str::of($value)
            ->explode("\n")
            ->map(fn($name) => trim($name))
            ->filter()
            ->values()
            ->toArray();

        foreach ($serverNames as $serverName) {
            // Remove all spaces from the server name
            $processedName = str_replace(' ', '', $serverName);

            // Check if the server name contains only English letters, numbers, and dots
            if (!preg_match('/^[a-zA-Z0-9.]+$/', $processedName)) {
                $fail("The server name '$serverName' must contain only English letters, numbers, and .");
                return;
            }
        }
    }
}

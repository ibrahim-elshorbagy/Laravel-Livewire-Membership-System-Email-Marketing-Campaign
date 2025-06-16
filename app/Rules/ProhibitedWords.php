<?php

namespace App\Rules;

use App\Models\Admin\Site\ProhibitedWord;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ProhibitedWords implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Get all prohibited words from the database (cached for performance)
        $prohibitedWords = cache()->remember('prohibited_words', now()->addMinutes(60), function () {
            return ProhibitedWord::pluck('word')->toArray();
        });

        // Collect prohibited words found in the input
        $foundWords = [];

        foreach ($prohibitedWords as $word) {
            if (stripos($value, $word) != false) {
                $foundWords[] = $word;
            }
        }

        // If prohibited words were found, return an error message listing them
        if (!empty($foundWords)) {
            $fail("The :attribute contains prohibited words: " . implode(', ', $foundWords));
        }
    }
}

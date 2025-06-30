<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UkgEmailDomainRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check if the value contains an @ symbol
        if (! str_contains($value, '@')) {
            $fail("The {$attribute} must be a valid email address.");

            return;
        }

        // Split the email and get the domain part
        $parts = explode('@', $value);

        // Ensure we have exactly 2 parts (local and domain)
        if (count($parts) !== 2) {
            $fail("The {$attribute} must be a valid email address.");

            return;
        }

        $domain = strtolower(trim($parts[1]));

        if ($domain !== 'ukg.com') {
            $fail("The {$attribute} must be a UKG company email address (@ukg.com).");
        }
    }
}

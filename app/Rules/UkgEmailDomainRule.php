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
        $domain = explode('@', $value)[1] ?? '';

        if (strtolower($domain) !== 'ukg.com') {
            $fail("The {$attribute} must be a UKG company email address (@ukg.com).");
        }
    }
}

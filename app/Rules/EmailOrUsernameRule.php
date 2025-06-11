<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Validator;

class EmailOrUsernameRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check if it's a valid email OR a valid username
        $isValidEmail = filter_var($value, FILTER_VALIDATE_EMAIL) !== false;

        $isValidUsername = !Validator::make(['username' => $value], [
            'username' => 'string|alpha_dash:2,25'
        ])->fails();

        // If neither is valid, fail validation
        if (!$isValidEmail && !$isValidUsername) {
            $fail('Please enter a valid email address or username.');
        }
    }
}

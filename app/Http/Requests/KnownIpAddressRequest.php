<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KnownIpAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start' => ['required', 'ipv4'],
            'end' => ['required', 'ipv4'],
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'min:5', 'max:255'],
        ];
    }
}

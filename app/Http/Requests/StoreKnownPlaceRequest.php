<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKnownPlaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9 ]+$/', // Only letters, spaces, and numbers
                'max:255',
                Rule::unique('known_places')->where('user_id', auth()->id())
            ],
            'description' => 'nullable|string|max:255',
            'latitude' => 'required|decimal:2,10|max:90',
            'longitude' => 'required|decimal:2,10|max:180',
            'radius' => 'required|integer',
            'accuracy' => 'required|integer|max:5000',
            'color' => 'nullable|hex_color',
            'locations' => ['nullable', 'array'],
            'locations.*' => [
                'required_with:locations',
                'array',
            ],
            'locations.*.*' => ['required_with:locations.*', 'string', 'regex:/^[A-Za-z0-9 ]+(?:\/[A-Za-z0-9 ]+)*$/'],
            'validation_order' => 'required|array',
            'validation_order.*' => [
                Rule::in(['gps', 'wifi']),
            ],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $validatedData = parent::validated($key, $default);
        return $validatedData;
    }
}

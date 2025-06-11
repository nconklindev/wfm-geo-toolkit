<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKnownPlaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ensure the user is authorized to update the specific KnownPlace instance
        // The controller already checks this, but double checking is good.
        $knownPlace = $this->route('knownPlace'); // Get the model instance from the route
        return $knownPlace && $this->user()->can('update', $knownPlace);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9 ]+$/', // Only letters, spaces, and numbers
                'max:255',
                Rule::unique('known_places')->where('user_id', auth()->id())->ignore($this->route('knownPlace'))
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
}

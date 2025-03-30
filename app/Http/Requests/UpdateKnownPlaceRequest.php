<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKnownPlaceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('known_places')
                    ->where('user_id', auth()->id())
                    ->ignore($this->knownPlace),
            ],
            'description' => 'nullable|string|max:255',
            'latitude' => 'required|decimal:2,10|max:90|min:-90',
            'longitude' => 'required|decimal:2,10|max:180|min:-180',
            'radius' => 'required|integer|max:2147483647',
            'accuracy' => 'required|integer|max:5000',
            // TODO: Add validation against added/imported Locations?
            'locations' => ['nullable', 'string'],
            'locations.*' => ['regex:/^[A-Za-z0-9 ]+(?:\/[A-Za-z0-9 ]+)*$/'],
            'validation_order' => 'required|array',
            'validation_order.*' => [
                Rule::in(['gps', 'wifi']),
            ],
        ];
    }
}

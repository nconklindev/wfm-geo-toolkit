<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWfmKnownPlaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
            'org_id' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
            'hostname' => 'required|string|url',
            'name' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9 ]+$/', // Only letters, spaces, and numbers
                'max:255',
            ],
            'description' => 'nullable|string|max:255',
            'latitude' => 'required|decimal:2,10|between:-90.00,90.00',
            'longitude' => 'required|decimal:2,10|between:-180.00,180.00',
            'radius' => 'required|integer|between:1,1000',
            'accuracy' => 'required|integer|between:1,1000',
        ];
    }
}

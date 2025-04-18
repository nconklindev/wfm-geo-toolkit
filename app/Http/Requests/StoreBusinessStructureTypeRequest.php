<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBusinessStructureTypeRequest extends FormRequest
{
    public function rules(): array
    {
        $userId = $this->user()->id;
        return [
            'name' => [
                'required',
                'string',
                'alpha_dash:ascii',
                'min:1',
                'max:50',
                Rule::unique('business_structure_types', 'name')
                    ->where('user_id', $userId),
            ],
            'description' => ['nullable', 'string', 'max:255', 'min:5'],
            'color' => ['nullable', 'hex_color'],
            'order' => [
                'required',
                'integer',
                'min:1',
                'max:9999',
                Rule::unique('business_structure_types', 'order')
                    ->where('user_id', $userId),
            ],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}

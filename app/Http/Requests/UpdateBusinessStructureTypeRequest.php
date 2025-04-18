<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBusinessStructureTypeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'alpha_dash:ascii',
                'min:1',
                'max:50',
                Rule::unique('business_structure_type',
                    'name')->ignore($this->user()->types()->find($this->route('type')))
            ],
            'description' => ['nullable', 'string', 'max:255', 'min:5'],
            'order' => [
                'required',
                'min:1',
                'max:9999',
                'integer',
                Rule::unique('business_structure_type', 'order')
                    ->ignore($this->user()->types()->find($this->route('type')))
            ],
            'color' => ['nullable', 'hex_color'],
        ];
    }

    public function authorize(): bool
    {
        $type = auth()->user()->types()->find($this->route('type'));
        return $type && $this->user()->can('update', $type);
    }
}

<?php

namespace App\Http\Requests;

use App\Models\BusinessStructureType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Import the Rule class

class ReassignAndDeleteTypeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        // Get the type being deleted from the route model binding
        /** @var BusinessStructureType $typeToDelete */
        $typeToDelete = $this->route('type');
        $userId = $this->user()->id; // Get the current user's ID
        $typeToDeleteId = $typeToDelete->id; // Get the ID to exclude

        return [
            // This field comes from the <select> in the confirm-delete form
            'replacement_type_id' => [
                'required', // Must select a replacement
                'integer',  // Must be an integer ID
                // Rule 1: Ensure the selected ID exists in the table and belongs to the current user
                Rule::exists('business_structure_types', 'id')
                    ->where('user_id', $userId),
                // Rule 2: Ensure the selected ID is not the one we are trying to delete
                Rule::notIn([$typeToDeleteId]),
            ],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Get the type instance directly from the route model binding
        /** @var BusinessStructureType $type */
        $type = $this->route('type');

        // Check if the authenticated user can 'delete' this specific type instance
        // This relies on the BusinessStructureTypePolicy's 'delete' method.
        return $this->user()->can('delete', $type);
    }
}

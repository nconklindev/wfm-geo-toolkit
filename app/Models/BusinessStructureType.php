<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class BusinessStructureType extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'name',
        'description',
        'hierarchy_order',
        'hex_color',
        'start_date',
        'end_date',
    ];

    public function toSearchableArray(): array
    {
        return [
            'id' => (int) $this->id,
            'name' => $this->name
        ];
    }

    public function businessStructureNodes(): HasMany
    {
        return $this->hasMany(BusinessStructureNode::class, 'business_structure_type_id');
    }

    protected function hexColor(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => str_starts_with($value, '#') ? $value : '#'.$value
        );
    }
}

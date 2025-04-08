<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class BusinessStructureType extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'name',
        'hierarchy_order',
    ];

    public function businessStructureNodes(): HasMany
    {
        return $this->hasMany(BusinessStructureNode::class, 'business_structure_type_id');
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => (int) $this->id,
            'name' => $this->name
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}

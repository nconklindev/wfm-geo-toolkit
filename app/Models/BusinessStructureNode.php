<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Kalnoy\Nestedset\NodeTrait;
use Laravel\Scout\Searchable;

class BusinessStructureNode extends Model
{
    use HasFactory, Searchable {
        Searchable::usesSoftDelete insteadof NodeTrait;
    }
    use NodeTrait;

    protected $fillable = [
        'name',
        'description',
        'business_structure_type_id',
        'parent_id',
        'path',
        'path_hierarchy',
        'structure_hash',
        'start_date',
        'end_date',
    ];

    public function children(): HasMany
    {
        return $this->hasMany(BusinessStructureNode::class, 'parent_id');
    }

    public function knownPlaces(): BelongsToMany
    {
        return $this->belongsToMany(KnownPlace::class)->withPivot('path', 'path_hierarchy')->withTimestamps();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(BusinessStructureNode::class, 'parent_id');
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'path' => $this->path_hierarchy
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(BusinessStructureType::class, 'business_structure_type_id');
    }

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'path_hierarchy' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }
}

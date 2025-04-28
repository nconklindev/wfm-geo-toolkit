<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Searchable;

class KnownPlace extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'name',
        'description',
        'latitude',
        'longitude',
        'radius',
        'is_active',
        'locations',
        'wifi_networks',
        'accuracy',
        'validation_order',
        'group_id',
        'color',
    ];

    protected $casts = [
        'validation_order' => 'array',
        'locations' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the leaf node (most specific location) for this known place
     * @return string|null
     */
    public function getLocationPathAttribute(): ?string
    {
        // Get the deepest node
        $leafNode = $this->nodes()->orderBy('_lft')->first(['id', 'path']);

        if (!$leafNode) {
            return null;
        }

        $ancestors = $leafNode->path;

        return $ancestors->pluck('name')->implode('/');
    }

    public function nodes(): BelongsToMany
    {
        return $this->belongsToMany(BusinessStructureNode::class)->withPivot('path',
            'path_hierarchy')->withTimestamps();
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */

    public function toSearchableArray(): array
    {
        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'locations' => $this->locations,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'user_id' => $this->user_id
        ];
    }
}

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

    protected $guarded = []; // TODO: We need to guard the user_id eventually
    protected $casts = [
        'validation_order' => 'array',
        'locations' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function businessNodes(): BelongsToMany
    {
        return $this->belongsToMany(BusinessStructureNode::class)->withTimestamps();
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

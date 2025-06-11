<?php

namespace App\Models;

use App\Observers\KnownPlaceObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Scout\Searchable;

#[ObservedBy(KnownPlaceObserver::class)]
class KnownPlace extends Model
{
    use HasFactory;
    use Notifiable;
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
        'color',
    ];

    protected $casts = [
        'validation_order' => 'array',
        'locations' => 'array',
    ];

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class)->withTimestamps();
    }

    public function nodes(): BelongsToMany
    {
        return $this->belongsToMany(BusinessStructureNode::class)->withPivot('path',
            'path_hierarchy')->withTimestamps();
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

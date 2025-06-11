<?php

namespace App\Http\Resources;

use App\Models\KnownPlace;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin KnownPlace */
class KnownPlaceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'radius' => $this->radius,
            'is_active' => (string) $this->is_active,
            'locations' => $this->locations,
            'accuracy' => $this->accuracy,
            'validation_order' => $this->validation_order,
            'color' => $this->color,
            'created_at' => $this->when($request->boolean('include_timestamps'), $this->created_at),
            'updated_at' => $this->when($request->boolean('include_timestamps'), $this->updated_at),
        ];
    }
}

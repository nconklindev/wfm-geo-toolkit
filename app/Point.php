<?php

namespace App;

use Livewire\Wireable;

class Point implements Wireable
{
    public function __construct(
        public float $latitude,
        public float $longitude,
        public string $label,
        public int $radius = 50,
        public int $accuracy = 100,
        public string $color = '#3b82f6',
    ) {}

    public static function fromLivewire($value): Point
    {
        $latitude = $value['latitude'];
        $longitude = $value['longitude'];
        $label = $value['label'];
        $radius = $value['radius'];
        $accuracy = $value['accuracy'];
        $color = $value['color'];

        return new static($latitude, $longitude, $label, $radius, $accuracy, $color);
    }

    public function toLivewire(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'label' => $this->label,
            'radius' => $this->radius,
            'accuracy' => $this->accuracy,
            'color' => $this->color,
        ];
    }
}

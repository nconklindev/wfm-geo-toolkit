<?php

namespace App\Services;

class BusinessStructureTypeService
{
    public function __construct()
    {
    }

    /**
     * @param  int  $position  The position in the hierarchy
     * @param  int  $total  Total number of colors needed
     * @return string Hex color code
     */
    public static function generateDistinctColor(int $position, int $total): string
    {
        // Golden ratio approximation for good distribution
        $goldenRatio = 0.618033988749895;
        $hue = fmod(($position * $goldenRatio), 1.0);

        // Convert to HSV then to RGB then to HEX
        $h = $hue;
        $s = 0.7; // Saturation
        $v = 0.95; // Value/brightness

        // HSV to RGB conversion
        $i = floor($h * 6);
        $f = $h * 6 - $i;
        $p = $v * (1 - $s);
        $q = $v * (1 - $f * $s);
        $t = $v * (1 - (1 - $f) * $s);

        switch ($i % 6) {
            case 0:
                $r = $v;
                $g = $t;
                $b = $p;
                break;
            case 1:
                $r = $q;
                $g = $v;
                $b = $p;
                break;
            case 2:
                $r = $p;
                $g = $v;
                $b = $t;
                break;
            case 3:
                $r = $p;
                $g = $q;
                $b = $v;
                break;
            case 4:
                $r = $t;
                $g = $p;
                $b = $v;
                break;
            case 5:
                $r = $v;
                $g = $p;
                $b = $q;
                break;
        }

        // Convert to hex
        $r = round($r * 255);
        $g = round($g * 255);
        $b = round($b * 255);

        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }

}

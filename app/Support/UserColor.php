<?php

namespace App\Support;

class UserColor
{
    /**
     * @var array<int, string>
     */
    private const PALETTE = [
        '#EF4444',
        '#F97316',
        '#F59E0B',
        '#84CC16',
        '#10B981',
        '#14B8A6',
        '#06B6D4',
        '#3B82F6',
        '#6366F1',
        '#8B5CF6',
        '#D946EF',
        '#EC4899',
    ];

    public static function normalize(?string $color): ?string
    {
        if ($color === null || $color === '') {
            return null;
        }

        $normalized = strtoupper($color);

        return preg_match('/^#[0-9A-F]{6}$/', $normalized) === 1
            ? $normalized
            : null;
    }

    /**
     * @param  array<int, string>  $usedColors
     */
    public static function generateUnique(array $usedColors, int $seed = 1): string
    {
        $usedLookup = array_fill_keys(array_map('strtoupper', $usedColors), true);

        foreach (self::PALETTE as $index => $color) {
            $candidate = self::PALETTE[($index + max(0, $seed - 1)) % count(self::PALETTE)];

            if (! isset($usedLookup[$candidate])) {
                return $candidate;
            }
        }

        $attempt = 0;

        do {
            $candidate = self::hslToHex(
                ($seed * 47 + $attempt * 29) % 360,
                72,
                58,
            );
            $attempt++;
        } while (isset($usedLookup[$candidate]));

        return $candidate;
    }

    private static function hslToHex(float $hue, float $saturation, float $lightness): string
    {
        $h = $hue / 360;
        $s = $saturation / 100;
        $l = $lightness / 100;

        if ($s == 0.0) {
            $value = (int) round($l * 255);

            return sprintf('#%02X%02X%02X', $value, $value, $value);
        }

        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - ($l * $s);
        $p = 2 * $l - $q;

        $red = self::hueToRgb($p, $q, $h + (1 / 3));
        $green = self::hueToRgb($p, $q, $h);
        $blue = self::hueToRgb($p, $q, $h - (1 / 3));

        return sprintf('#%02X%02X%02X', (int) round($red * 255), (int) round($green * 255), (int) round($blue * 255));
    }

    private static function hueToRgb(float $p, float $q, float $t): float
    {
        if ($t < 0) {
            $t += 1;
        }

        if ($t > 1) {
            $t -= 1;
        }

        if ($t < 1 / 6) {
            return $p + ($q - $p) * 6 * $t;
        }

        if ($t < 1 / 2) {
            return $q;
        }

        if ($t < 2 / 3) {
            return $p + ($q - $p) * ((2 / 3) - $t) * 6;
        }

        return $p;
    }
}

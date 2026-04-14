<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<int, string>
     */
    private array $palette = [
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

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('color', 7)->nullable()->after('email');
        });

        $usedColors = [];

        DB::table('users')
            ->orderBy('id')
            ->get(['id', 'color'])
            ->each(function (object $user) use (&$usedColors): void {
                $normalized = $this->normalizeColor($user->color);

                if ($normalized === null || in_array($normalized, $usedColors, true)) {
                    $normalized = $this->generateUniqueColor($usedColors, (int) $user->id);
                }

                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['color' => $normalized]);

                $usedColors[] = $normalized;
            });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('color');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['color']);
            $table->dropColumn('color');
        });
    }

    private function normalizeColor(?string $color): ?string
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
    private function generateUniqueColor(array $usedColors, int $seed): string
    {
        foreach ($this->palette as $index => $color) {
            $candidate = $this->palette[($index + max(0, $seed - 1)) % count($this->palette)];

            if (! in_array($candidate, $usedColors, true)) {
                return $candidate;
            }
        }

        $attempt = 0;

        do {
            $candidate = $this->hslToHex(
                ($seed * 47 + $attempt * 29) % 360,
                72,
                58,
            );
            $attempt++;
        } while (in_array($candidate, $usedColors, true));

        return $candidate;
    }

    private function hslToHex(float $hue, float $saturation, float $lightness): string
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

        $red = $this->hueToRgb($p, $q, $h + (1 / 3));
        $green = $this->hueToRgb($p, $q, $h);
        $blue = $this->hueToRgb($p, $q, $h - (1 / 3));

        return sprintf('#%02X%02X%02X', (int) round($red * 255), (int) round($green * 255), (int) round($blue * 255));
    }

    private function hueToRgb(float $p, float $q, float $t): float
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
};

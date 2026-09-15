<?php

namespace App\Support\Calendar;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class MalaysiaHolidays
{
    public function forYear(int $year): array
    {
        $key = 'malaysia-holidays:v1:'.$year;
        $result = Cache::get($key);
        if (! is_array($result)) {
            try {
                $payload = Http::acceptJson()->connectTimeout(2)->timeout(5)
                    ->get(rtrim(config('calendar.holiday_api_url'), '/').'/holidays', ['year' => $year])
                    ->throw()->json();
                if (! is_array($payload['data'] ?? null)) {
                    throw new RuntimeException('Invalid holiday response.');
                }
                $holidays = [];
                foreach ($payload['data'] as $item) {
                    if (! is_array($item) || ! is_string($item['name'] ?? null) || ! is_string($item['date'] ?? null)
                        || ! is_array($item['state_codes'] ?? null)
                        || ! preg_match('/^'.$year.'-\d{2}-\d{2}$/', $item['date'])) {
                        throw new RuntimeException('Invalid holiday record.');
                    }
                    [, $month, $day] = explode('-', $item['date']);
                    if (! checkdate((int) $month, (int) $day, $year)) {
                        throw new RuntimeException('Invalid holiday date.');
                    }
                    $states = array_values(array_intersect(array_keys(config('calendar.states')), $item['state_codes']));
                    if (! $states) continue;
                    $holidays[] = [
                        'date' => $item['date'], 'name' => $item['name'], 'kind' => 'holiday',
                        'states' => $states, 'nationwide' => count($states) === count(config('calendar.states')),
                        'tentative' => (bool) ($item['is_subject_to_change'] ?? false),
                    ];
                }
                $result = ['status' => count($holidays) ? 'ready' : 'empty', 'holidays' => $holidays];
                Cache::put($key, $result, now()->addDay());
                if ($holidays) Cache::put($key.':stale', $result, now()->addDays(30));
            } catch (Throwable $exception) {
                $result = Cache::get($key.':stale', ['holidays' => []]);
                $result['status'] = count($result['holidays']) ? 'stale' : 'unavailable';
                Cache::put($key, $result, now()->addMinutes(5));
            }
        }
        foreach (config('calendar.observances', []) as $observance) {
            $result['holidays'][] = [
                'date' => $year.'-'.$observance['date'], 'name' => $observance['name'],
                'kind' => 'observance', 'states' => [], 'nationwide' => true, 'tentative' => false,
            ];
        }

        return $result;
    }
}

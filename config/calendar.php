<?php

return [
    'holiday_api_url' => env('MALAYSIA_HOLIDAY_API_URL', 'https://malaysia-holiday.dydxsoft.my/api/v1'),
    // State codes published by https://malaysia-holiday.dydxsoft.my/api/docs.
    'states' => [
        'JHR' => 'Johor', 'KDH' => 'Kedah', 'KTN' => 'Kelantan', 'MLK' => 'Melaka',
        'NSN' => 'Negeri Sembilan', 'PHG' => 'Pahang', 'PRK' => 'Perak', 'PLS' => 'Perlis',
        'PNG' => 'Pulau Pinang', 'SBH' => 'Sabah', 'SWK' => 'Sarawak', 'SGR' => 'Selangor',
        'TRG' => 'Terengganu', 'KUL' => 'Kuala Lumpur', 'LBN' => 'Labuan', 'PJY' => 'Putrajaya',
    ],
    // Observances, not public holidays. Sources: UN observances and Malaysia KPM.
    'observances' => [
        ['date' => '03-08', 'name' => "International Women's Day", 'source' => 'https://www.un.org/en/observances/womens-day'],
        ['date' => '05-16', 'name' => 'Hari Guru', 'source' => 'https://www.moe.gov.my/sambutan-hari-guru-peringkat-kebangsaan-kali-ke-55'],
        ['date' => '06-05', 'name' => 'World Environment Day', 'source' => 'https://www.un.org/en/observances/environment-day'],
        ['date' => '09-21', 'name' => 'International Day of Peace', 'source' => 'https://www.un.org/en/observances/international-day-peace'],
    ],
];

<?php

return [
    'providers' => [
        'ipstack' => [
            'key' => env('IPSTACK_API_KEY', ''),
            'endpoint' => env('IPSTACK_ENDPOINT', 'https://api.ipstack.com'),
        ],
        'ipinfo' => [
            'key' => env('IPINFO_API_KEY', ''),
            'endpoint' => env('IPINFO_ENDPOINT', 'https://ipinfo.io'),
        ],
    ],
    'cache_ttl' => env('IP_CACHE_TTL', 604800), // 7 days
];

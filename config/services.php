<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | External Logistics & Telemetry APIs
    |--------------------------------------------------------------------------
    */

    'weather' => [
        'url' => env('WEATHER_API_URL', 'https://api.open-meteo.com/v1/forecast'),
        'api_key' => env('OPENWEATHER_API_KEY'),
    ],

    'timezone' => [
        'url' => env('TIMEZONE_API_URL', 'https://timeapi.io/api/time/current/coordinate'),
    ],

    'currency' => [
        'url' => env('CURRENCY_API_URL', 'https://open.er-api.com/v6/latest/IDR'),
        'api_key' => env('EXCHANGERATE_API_KEY'),
    ],

    'geocoding' => [
        'url' => env('GEOCODING_API_URL', 'https://nominatim.openstreetmap.org/search'),
    ],

    'reliefweb' => [
        'url' => env('RELIEFWEB_API_URL', 'https://api.reliefweb.int/v1/reports'),
    ],

    'aviation' => [
        'opensky_url' => env('OPENSKY_API_URL', 'https://opensky-network.org/api/states/all'),
        'username' => env('OPENSKY_CLIENT_USERNAME'),
        'password' => env('OPENSKY_CLIENT_PASSWORD'),
    ],

    'routing' => [
        'osrm_url' => env('ROUTING_OSRM_URL', 'https://router.project-osrm.org/route/v1/driving'),
    ],

];

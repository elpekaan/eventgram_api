<?php

use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;

return [
    /*
    |--------------------------------------------------------------------------
    | Scramble Page Path
    |--------------------------------------------------------------------------
    |
    | Dokümantasyonun hangi adreste çalışacağını belirler.
    | Örn: localhost:8000/docs/api
    |
    */
    'ui_path' => 'docs/api',

    /*
    |--------------------------------------------------------------------------
    | API Path
    |--------------------------------------------------------------------------
    |
    | Sadece bu prefix ile başlayan rotaları dokümante et.
    | Web rotalarını (/) karıştırmasın.
    |
    */
    'api_path' => 'api',

    /*
    |--------------------------------------------------------------------------
    | API Domain
    |--------------------------------------------------------------------------
    */
    'api_domain' => null,

    /*
    |--------------------------------------------------------------------------
    | Information
    |--------------------------------------------------------------------------
    */
    'info' => [
        'version' => '1.0.0',
        'description' => 'Eventgram Bilet Satış Platformu API Dokümantasyonu',
    ],

    /*
    |--------------------------------------------------------------------------
    | Server Configuration
    |--------------------------------------------------------------------------
    */
    'servers' => [
        'Local' => 'http://localhost:8000/api', // Veya Herd kullanıyorsan domainin
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Doküman sayfasına kimler erişebilir? (Production'da şifrelemek istersen buraya bakarsın)
    |
    */
    'middleware' => [
        'web',
    ],

    /*
    |--------------------------------------------------------------------------
    | Extensions
    |--------------------------------------------------------------------------
    */
    'extensions' => [],
];

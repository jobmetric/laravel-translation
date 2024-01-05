<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cache Time
    |--------------------------------------------------------------------------
    |
    | Cache time for get data translation
    |
    | - set zero for remove cache
    | - set null for forever
    |
    | - unit: minutes
    */

    "cache_time" => env("TRANSLATION_CACHE_TIME", 0),

    /*
    |--------------------------------------------------------------------------
    | Table Name
    |--------------------------------------------------------------------------
    |
    | Table name in database
    */

    "tables" => [
        'translation' => 'translations'
    ],

];

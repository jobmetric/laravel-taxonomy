<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Taxonomy Types
    |--------------------------------------------------------------------------
    */

    'types' => [],

    /*
    |--------------------------------------------------------------------------
    | Cache Time
    |--------------------------------------------------------------------------
    |
    | Cache time for get data taxonomy
    |
    | - set zero for remove cache
    | - set null for forever
    |
    | - unit: minutes
    */

    "cache_time" => env("TAXONOMY_CACHE_TIME", 0),

    /*
    |--------------------------------------------------------------------------
    | Table Name
    |--------------------------------------------------------------------------
    |
    | Table name in database
    */

    "tables" => [
        'taxonomy' => 'taxonomies',
        'taxonomy_path' => 'taxonomy_paths',
        'taxonomy_relation' => 'taxonomy_relations'
    ],

    /*
    |--------------------------------------------------------------------------
    | Arrow Icon
    |--------------------------------------------------------------------------
    |
    | Arrow icon for show taxonomy tree
    */

    "arrow_icon" => [
        'rtl' => env('TAXONOMY_ARROW_ICON_RTL', ' ◄ '),
        'ltr' => env('TAXONOMY_ARROW_ICON_LTR', ' ► '),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Image Size
    |--------------------------------------------------------------------------
    |
    | Default image size for media
    */

    "default_image_size" => [
        'width' => env('TAXONOMY_DEFAULT_IMAGE_SIZE_WIDTH', 100),
        'height' => env('TAXONOMY_DEFAULT_IMAGE_SIZE_HEIGHT', 100),
    ],

];

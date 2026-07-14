<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Define prefix and middleware for package web routes and API routes.
    |
    */
    'route_prefix'     => 'dashboard',
    'route_middleware' => ['web', 'auth'],
    'api_prefix'       => 'api/dashboard',
    'api_middleware'   => ['api', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Caching configuration for dashboard queries and schemas.
    |
    */
    'cache_driver'     => env('DASHBOARD_CACHE_DRIVER', 'redis'),
    'default_ttl'      => 300, // in seconds
    'queue_connection' => env('DASHBOARD_QUEUE', 'default'),

    /*
    |--------------------------------------------------------------------------
    | UI & Visual Theme
    |--------------------------------------------------------------------------
    |
    | Configuration for theme styles, grids, and frontend dependencies.
    |
    */
    'theme'            => 'dark',    // 'dark' | 'light' | 'auto'
    'grid_columns'     => 12,
    'grid_row_height'  => 80,        // px
    'chart_library'    => 'echarts', // 'echarts' is the default
    'flowchart_library'=> 'x6',      // 'x6' | 'reactflow'
    'richtext_editor'  => 'tiptap',  // 'tiptap' | 'quill'

    /*
    |--------------------------------------------------------------------------
    | Registered Modules (Eloquent Models)
    |--------------------------------------------------------------------------
    |
    | Registers models available for visual query building and widgets.
    | Set fields => 'auto' to auto-discover casts and db column types.
    |
    */
    'modules' => [
        // Example:
        // 'opportunities' => [
        //     'label' => 'Opportunities',
        //     'model' => 'App\\Models\\Opportunity',
        //     'icon'  => 'currency-dollar',
        //     'fields' => 'auto',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions & Gates
    |--------------------------------------------------------------------------
    |
    | Access controls for viewing, editing, or managing dashboards.
    | Driver: 'spatie' | 'gate' | 'none'
    |
    */
    'permissions_driver' => 'none',
    'admin_gate'         => 'manage-dashboards',

    /*
    |--------------------------------------------------------------------------
    | Security Config
    |--------------------------------------------------------------------------
    |
    | Adjust safety levels for raw queries, input sanitization, etc.
    |
    */
    'allow_raw_sql'      => false,
    'iframe_allowlist'   => [],
    'sanitize_richtext'  => true,

];

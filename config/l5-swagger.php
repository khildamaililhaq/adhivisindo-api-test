<?php

return [
    'api' => [
        'title' => 'L5 Swagger UI',
    ],

    'routes' => [
        'api' => 'api/documentation',
        'docs' => 'docs',
        'oauth2_callback' => 'api/oauth2-callback',
        'middleware' => [
            'api' => [],
            'asset' => [],
            'docs' => [],
            'oauth2_callback' => [],
        ],
    ],

    'paths' => [
        'docs' => storage_path('api-docs'),
        'docs_json' => 'api-docs.json',
        'docs_yaml' => 'api-docs.yaml',
        'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),

        'annotations' => [
            'scan' => [
                app_path('Http/Controllers'),
            ],
        ],

        'generates' => true,
        'generate_always' => env('L5_GENERATE_ALWAYS', false),
        'generate_yaml_copy' => env('L5_GENERATE_YAML_COPY', false),
        'proxy' => false,
        'additional_config_url' => null,
        'operations_sort' => env('L5_OPERATIONS_SORT', null),
        'variables' => [
            'variable' => [
                'name' => 'Authorization',
                'type' => 'apiKey',
                'description' => 'Enter your API key',
                'in' => 'header',
            ],
        ],
    ],

    'swagger_version' => env('SWAGGER_VERSION', '3.0'),

    'security' => [
        'enabled' => false,
    ],
];
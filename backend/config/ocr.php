<?php

return [

    'last_matches_limit' => env('OCR_LAST_MATCHES_LIMIT', 10),

    /*
    |--------------------------------------------------------------------------
    | Threshold for string matching
    |--------------------------------------------------------------------------
    | Maximum allowed distance for a match. Lower values = stricter matching.
    */
    'levenshtein_threshold' => env('OCR_THRESHOLD', 2),

    /*
    |--------------------------------------------------------------------------
    | Comparison method
    |--------------------------------------------------------------------------
    | Options: levenshtein, similar_text
    */
    'comparison_method' => env('OCR_COMPARISON', 'levenshtein'),

    /*
    |--------------------------------------------------------------------------
    | Field-specific thresholds (optional)
    |--------------------------------------------------------------------------
    | You can set different thresholds for specific fields.
    */
    'field_thresholds' => [
        'plate_number'    => env('OCR_PLATE_THRESHOLD', 1),
        'container_code'  => env('OCR_CONTAINER_THRESHOLD', 2),
    ],

    'custom_tariff' => env('CUSTOM_TARIFF', 1715700)
];

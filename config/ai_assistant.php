<?php

return [
    'api_key' => env('OPENAI_API_KEY'),
    'model' => env('OPENAI_MODEL', 'gpt-5'),
    'base_uri' => 'https://api.openai.com/v1/',
    'timeout' => (int) env('OPENAI_TIMEOUT', 60),
    'file_search_max_results' => (int) env('OPENAI_FILE_SEARCH_MAX_RESULTS', 5),
];
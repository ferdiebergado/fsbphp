<?php
return [
    'name' => 'fsb_app1',
    'save_path' => CACHE_PATH . 'sessions',
    'sid_length' => 36,
    'cookie' => [
        'lifetime' => 60 * 60 * 60 * 2,
        'httponly' => true,
    ]
];

<?php

/**
 * SESSION SETTINGS
 * @return array
 */

return [
    'session' => [
        'name' => 'fsb_app1',
        'segment' => 'FSB',
        'save_path' => CACHE_PATH . 'sessions',
        'sid_length' => 36,
        'cookie' => [
            'lifetime' => 60 * 60 * 60 * 2, // 2 hours
            'httponly' => true,
        ]
    ]
];
        
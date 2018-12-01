<?php
return [
    'cache' => [
        'enabled' => true,
        'path' => '',
        'expire' => 30, //minutes
        'prefix_key' => 'apix-cache-key:', // prefix cache keys
        'prefix_tag' => 'apix-cache-tag:', // prefix cache tags
        'tag_enable' => true,               // wether to enable tags support
        'directory' => CACHE_PATH . 'cache', // Directory where the cache is created
        'locking' => true
    ]
];

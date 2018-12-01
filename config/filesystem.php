<?php

return [
    'flysystem' => [
        'adaptors' => [
            // Array Keys are the names used for the adaptor.  Default entry required for adaptors
            'default' => [
                'type' => 'local',
                'options' => [
                    'root' => '/path/to/root', // Required : Path on local filesystem
                    'writeFlags' => LOCK_EX,   // Optional : PHP flags.  See: file_get_contents for more info
                    'linkBehavior' => League\Flysystem\Adapter\Local::DISALLOW_LINKS, // Optional : Link behavior
                    
                    // Optional:  Optional set of permissions to set for files
                    'permissions' => [
                        'file' => [
                            'public' => 0644,
                            'private' => 0600,
                        ],
                        'dir' => [
                            'public' => 0755,
                            'private' => 0700,
                        ]
                    ]
                ],
            ],
        ],

        'caches' => [
            // Array Keys are the names used for the cache
            //
            // Note: You can specify "default" here to overwrite the default settings for the
            // default cache.  Memory is used if not specified
            'default' => [
                'type' => 'psr6',
                // Cache specific options.  See caches below
                'options' => [
                    'service' => 'my_psr6_service_from_container',
                    'key' => 'my_key_',
                    'ttl' => 3000
                ],
            ],

            'cacheTwo' => [
                'type' => 'psr6',
                // Cache specific options.  See caches below
                'options' => [
                    'service' => 'my_psr6_service_from_container',
                    'key' => 'my_key_',
                    'ttl' => 3000
                ],
            ],

        ],

        'fileSystems' => [
            // Array Keys are the file systems identifiers.
            //
            // Note: You can specify "default" here to overwrite the default settings for the
            // default file system
            'default' => [
                'adaptor' => 'default', // Adaptor name from adaptor configuration
                'cache' => 'default',   // Cache name from adaptor configuration
                'plugins' => []         // User defined plugins to be injected into the file system
            ],
            
            // Mount Manager Config
            'manager' => [
                'adaptor' => 'manager',
                'fileSystems' => [
                    'local' => [
                        'adaptor' => 'default', // Adaptor name from adaptor configuration
                        'cache' => 'default',   // PSR-6 pre-configured service
                        'plugins' => []         // User defined plugins to be injected into the file system
                    ],

                    'anotherFileSystem' => [
                        'adaptor' => 'adaptorTwo', // Adaptor name from adaptor configuration
                        'cache' => 'cacheTwo',     // PSR-6 pre-configured service
                        'plugins' => []            // User defined plugins to be injected into the file system
                    ],
                ],
            ],
        ],
    ],
];

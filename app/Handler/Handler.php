<?php

namespace App\Handler;

use FSB\Cache\Cache;

abstract class Handler
{
    protected $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }
}

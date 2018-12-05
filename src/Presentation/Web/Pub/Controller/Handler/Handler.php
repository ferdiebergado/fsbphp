<?php

namespace Bergado\Presentation\Web\Pub\Controller\Handler;

use Bergado\Infrastructure\Cache\Cache;

abstract class Handler
{
    protected $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }
}

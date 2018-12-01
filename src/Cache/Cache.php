<?php declare (strict_types = 1);

namespace FSB\Cache;

use Closure;
use Apix\Cache\Files;

class Cache
{
    private $cache;

    public function __construct(Files $cache)
    {
        $this->cache = $cache;
    }

    public function recall(string $key, int $expire, $value)
    {
        if (!$data = $this->cache->load($key)) {
            $data = $value;
            if (is_callable($value)) {
                $data = call_user_func($value);
            }
            $tags = explode($key, '_');
            $this->cache->save($data, $key, array($tags[0]), $expire * 60);
        }
        return $data;
    }

    public function delete($key)
    {
        $this->cache->delete($key);
    }

    public function __call($name, $arguments)
    {
        $this->cache->{$name}($arguments);
    }
}

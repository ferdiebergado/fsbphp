<?php

namespace FSB\Router\Map;

use Aura\Router\Map;

class ResourceMap extends Map
{
    public function resource($namePrefix, $pathPrefix, $handler)
    {
        return $this->attach($namePrefix, $pathPrefix, function ($map) use ($handler) {
            $browse = 'browse';
            $read = 'read';
            $modify = 'modify';
            $add = 'add';
            $remove = 'remove';
            $map->auth(['loggedIn' => true]);
            $map->get($browse, '', [$handler, $browse]);
            $map->get($read, '/{id}', [$handler, $read]);
            $map->patch($modify, '/{id}', [$handler, $modify]);
            $map->post($add, '', [$handler, $add]);
            $map->delete($remove, '/{id}', [$handler, $remove]);
        });
    }
}

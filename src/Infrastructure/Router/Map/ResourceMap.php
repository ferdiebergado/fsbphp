<?php

namespace Bergado\Infrastructure\Router\Map;

use Aura\Router\Map;

class ResourceMap extends Map
{
    public function resource($namePrefix, $pathPrefix, $handler)
    {
        return $this->attach($namePrefix, $pathPrefix, function ($map) use ($handler) {
            $browse = 'browse';
            $read = 'read';
            $edit = 'edit';
            $update = 'update';
            $add = 'add';
            $save = 'save';
            $remove = 'remove';
            $map->auth(['loggedIn' => true]);
            $map->get($browse, '', [$handler, $browse]);
            $map->get($read, '/{id}', [$handler, $read]);
            $map->get($edit, '/{id}/' . $edit, [$handler, $edit]);
            $map->put($update, '/{id}', [$handler, $update]);
            $map->get($add, '/' . $add, [$handler, $add]);
            $map->post($save, '/' . $add, [$handler, $save]);
            $map->delete($remove, '/{id}', [$handler, $remove]);
        });
    }
}

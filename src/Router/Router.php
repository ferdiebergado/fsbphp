<?php

namespace FSB\Router;

use FSB\Router\Rule\Auth;
use Aura\Router\RouterContainer;
use FSB\Router\Rule\Guest;

class Router
{
    private $router;

    public function __construct(RouterContainer $router)
    {
        $this->router = $router;
    }

    public function start()
    {
        // $router = $container->get('router');
        $this->router->getRuleIterator()->set([
            new Auth(),
            // new Guest(),
        ]);
        if (!DEBUG_MODE) {

            $this->router->setMapBuilder(function ($map) {
                
                // the cache file location
                $cache = CACHE_PATH . 'routes' . DS . 'routes.cache';
                
                // does the cache exist?
                if (file_exists($cache)) {
                    // restore from the cache
                    $routes = unserialize(file_get_contents($cache));
                    $map->setRoutes($routes);

                } else {
                    
                    // build the routes on the map ...
                    // $map->get(...);
                    // $map->post(...);
                    // $map = $router->getMap();
                    include(CONFIG_PATH . 'routes.php');
                    // ... then save them to the cache for the next page load
                    $routes = $map->getRoutes();
                    file_put_contents($cache, serialize($routes));
                }
            });
        }
        $map = $this->router->getMap();
        include(CONFIG_PATH . 'routes.php');
        return $this;
    }
}
    
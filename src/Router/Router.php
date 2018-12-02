<?php

namespace FSB\Router;

use Aura\Router\Route;
use FSB\Router\Map\ResourceMap;
use Aura\Router\RouterContainer;
use FSB\Router\Route\ModelRoute;
use Aura\Router\Rule\RuleInterface;

class Router
{
    private $router;
    private $rules;

    public function __construct(RouterContainer $router, RuleInterface ...$rules)
    {
        $this->router = $router;
        $this->rules = $rules;
    }

    public function start()
    {
        $this->router->setMapFactory(function () {
            return new ResourceMap(new Route());
        });
        $this->router->setRouteFactory(function () {
            return new ModelRoute();
        });
        if (isset($this->rules)) {
            $rules = $this->router->getRuleIterator();
            foreach ($this->rules as $rule) {
                $rules->append($rule);
            }
        }
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
        } else {
            $map = $this->router->getMap();
            include(CONFIG_PATH . 'routes.php');
        }
    }
}

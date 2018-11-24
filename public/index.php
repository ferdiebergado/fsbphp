<?php

/*** FRONT CONTROLLER ***/

/* Delegate static file requests back to the PHP built-in webserver */
if (PHP_SAPI === 'cli-server' && $_SERVER['SCRIPT_FILENAME'] !== __FILE__) {
    return false;
}

/* Global constants */
define('FSB_TIME', microtime(true));
define('DEBUG_MODE', true);
define('DS', DIRECTORY_SEPARATOR);
define('BASE_PATH', __DIR__ . DS . '..' . DS);
define('CONFIG_PATH', BASE_PATH . 'config' . DS);
define('CACHE_PATH', BASE_PATH . 'cache' . DS);
define('VIEW_PATH', BASE_PATH . 'app' . DS . 'View' . DS);

/* Autoload libraries */
require BASE_PATH . 'vendor' . DS . 'autoload.php';

/* Register the Error Handler */
// if (DEBUG_MODE) {
error_reporting(E_ALL);
$whoops = new Whoops\Run;
$whoops->pushHandler(new Whoops\Handler\PrettyPageHandler);
$whoops->register();
// }

$c = new FSB\Container();
$request = $c->get('request');
$router = $c->get('router');
$router
    ->middleware($c->get('mw_session'))
    ->middleware($c->get('content-type'))
    ->middleware($c->get('headers'));

$routes = include(CONFIG_PATH . 'routes.php');

/* Send the response to the client */
$response = $router->dispatch($request);
return Http\Response\send($response);

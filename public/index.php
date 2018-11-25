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
define('DATE_FORMAT_SHORT', 'Y-m-d h:i:s');
define('DATE_FORMAT_LONG', 'Y-m-d h:i:s A e');
define('LOG_FILE', CACHE_PATH . 'app_' . date('Y') . '.log');

/* Autoload libraries */
require BASE_PATH . 'vendor' . DS . 'autoload.php';

/* Register the error handler */
error_reporting(E_ALL);
$whoops = new Whoops\Run;
use Whoops\Handler\PrettyPageHandler;

$pagehandler = new PrettyPageHandler;

if (DEBUG_MODE) {
    $whoops->pushHandler($pagehandler);
} else {
    $whoops->pushHandler(function ($e) use ($whoops, $pagehandler) {
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);
        $whoops->pushHandler($pagehandler);
        $body = $whoops->handleException($e);
        // $app = require(CONFIG_PATH . 'app.php');
        // Core\Mail::send($app['author_email'], $app['name'] . ' Error Exception', $body);
        logger($e->getMessage(), 2);
        // require VIEW_PATH . '500.php';
    });
}
$whoops->register();

/** Instantiate the DI Container
 * @var Psr\Container\ContainerInterface $c
 */
$c = new FSB\Container();

/** Initialize the Router
 * @var League\Route\Router $router
 * @var League\Route\Route $routes
 */
$router = $c->get('router');
$router
    ->middleware($c->get('headers'))
    ->middleware($c->get('content-type'))
    ->middleware($c->get('mw_session'));
$routes = include(CONFIG_PATH . 'routes.php');

/** Create a Server Request
 * @var Psr\Http\Message\ServerRequestInterface $request
 */
$request = $c->get('request');

/** Send the response to the client
 * @var Psr\Http\Message\ResponseInterface $response
 */
$response = $router->dispatch($request);
return Http\Response\send($response);

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
use Whoops\Handler\PrettyPageHandler;

$whoops = new Whoops\Run;
$pagehandler = new PrettyPageHandler;

if (DEBUG_MODE) {
    error_reporting(E_ALL);
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
        require VIEW_PATH . 'errors/500.php';
    });
}
$whoops->register();

/** Instantiate the DI Container
 * @var Psr\Container\ContainerInterface $c */
$container = new FSB\Container();

/** Create a Server Request
 * @var Psr\Http\Message\ServerRequestInterface $request */
$request = $container->get('request');

/** Load the application routes  
 * @var \FSB\Router\Router $router */
$router = $container->get('router');
$router->start();

/** Dispatch the middleware stack
 * @var array $middlewares
 * @var \Middleland\Dispatcher $dispatcher
 * @var Psr\Http\Message\ResponseInterface $response */
$middlewares = include(CONFIG_PATH . 'middlewares.php');
$dispatcher = new Middleland\Dispatcher($middlewares, $container);
$response = $dispatcher->dispatch($request);

/** Send the response to the client 
 * @var \Zend\HttpHandlerRunner\Emitter\SapiEmitter $emitter */
$emitter = new Zend\HttpHandlerRunner\Emitter\SapiEmitter;
return $emitter->emit($response);

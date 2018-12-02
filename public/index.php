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

/** Instantiate the DI Container
 * @var Psr\Container\ContainerInterface $c */
$container = new FSB\Container();

/** Register the Exception Handler 
 * @var \FSB\Exception\ExceptionHandler $exceptionHandler */
$exceptionHandler = $container->get('exception-handler');
$exceptionHandler->register();

/** Load the application routes  
 * @var \FSB\Router\Router $router */
$router = $container->get('router');
$router->start();

/** Create a Server Request
 * @var Psr\Http\Message\ServerRequestInterface $request */
$request = $container->get('request');

/** Dispatch the middleware stack
 * @var \Relay\Relay $dispatcher
 * @var \Psr\Http\Message\ResponseInterface $response */
$dispatcher = $container->get('dispatcher');
$response = $dispatcher->handle($request);

/** Send the response to the client 
 * @var \Zend\HttpHandlerRunner\Emitter\SapiEmitter $emitter */
$emitter = $container->get('emitter');
return $emitter->emit($response);

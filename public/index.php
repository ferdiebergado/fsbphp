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

/* Dispatch the middlewares */
$container = new FSB\Container();
$request = $container->get('request');
$dispatcher = $container->get('dispatcher');
$middlewares = require(CONFIG_PATH . 'middleware.php');
foreach ($middlewares as $m) {
    $dispatcher->append($m);
}

/* Send the response to the client */
$response = $dispatcher->handle($request);
$status = $response->getStatusCode();
$errors = [
    '404',
    '405'
];
if (in_array($status, $errors)) {
    $err_view_path = "errors/";
    $viewfile = "{$err_view_path}404";
    $template = $container->get('template');
    $view = $template->render($viewfile . ".html.twig", array());
    $response->getBody()->write($view);
}
return Http\Response\send($response);

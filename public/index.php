<?php

/**
 * bergadophp - A PHP Web Application Skeleton
 *
 * @package  bergadophp
 * @author   Ferdinand Saporas Bergado <ferdiebergado@gmail.com>
 * 
 * MIT License
 * 
 * Copyright (c) 2018 Ferdinand Saporas Bergado
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

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
 * @var \Zend\HttpHandlerRunner\Emitter\EmitterStack $stack
 * @var \Zend\HttpHandlerRunner\Emitter\SapiEmitter $sapiEmitter 
 * @var \FSB\Emitter\ConditionalEmitter $conditionalEmitter */
$stack = $container->get('emitter-stack');
$sapiEmitter = $container->get('emitter');
$conditionalEmitter = $container->get('conditional-emitter');
$stack->push($sapiEmitter);
$stack->push($conditionalEmitter);
return $stack->emit($response);

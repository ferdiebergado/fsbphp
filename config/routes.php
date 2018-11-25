<?php

/**
 * APPLICATION ROUTES 
 * 
 * @var League\Route\Router $router
 * @var Psr\Container\ContainerInterface $c
 * 
 * @return null
 * */

$namespace = "App\\Controller\\";

$router->get('/', $namespace . 'HomeController::index')->middleware($c->get('auth'));
$router->get('/login', $namespace . 'LoginController::show')
    ->middleware($c->get('guest'));
$router->post('/login', $namespace . 'LoginController::login')
    ->middleware($c->get('guest'))
    ->middleware($c->get('csrf'));
$router->post('/logout', $namespace . 'LoginController::logout')
    ->middleware($c->get('auth'))
    ->middleware($c->get('csrf'));
$router->get('/users/{user}', $namespace . 'UserController::show')
    ->middleware($c->get('auth'));

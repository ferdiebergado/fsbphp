<?php

namespace FSB\Middleware;

use League\Route\Router;
use FSB\Session\SessionHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use League\Route\Http\Exception\NotFoundException;
use League\Route\Http\Exception\MethodNotAllowedException;
use Psr\Http\Message\ResponseFactoryInterface;

class RouterMiddleware extends Middleware implements MiddlewareInterface
{
    private $router;

    public function __construct(Router $router, ResponseFactoryInterface $responseFactory)
    {
        parent::__construct($responseFactory);
        $this->router = $router;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        try {
            $response = $this->router->dispatch($request);
            return $response;
        } catch (NotFoundException $e) {
            return $this->response->withStatus(404);
        } catch (MethodNotAllowedException $e) {
            $headers = $e->getHeaders();
            return $this->response->withStatus(405)->withHeader(array_keys($headers)[0], array_values($headers)[0]);
        } catch (\Exception $e) {
            $session = new SessionHelper($request);
            $session->flash('error', $e->getMessage());
            return $this->response->withStatus(501)->withHeader('Location', '/');
        }
    }
}

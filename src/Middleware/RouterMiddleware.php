<?php

namespace FSB\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use League\Route\Http\Exception\NotFoundException;
use League\Route\Http\Exception\MethodNotAllowedException;
use FSB\Session\SessionHelper;

class RouterMiddleware extends Middleware implements MiddlewareInterface
{
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        try {
            $response = $this->router->dispatch($request);
            return $response;
        } catch (NotFoundException $e) {
            return $this->response->withStatus('404');
        } catch (MethodNotAllowedException $e) {
            $headers = $e->getHeaders();
            return $this->response->withStatus(405)->withHeaders(array_keys($headers)[0], array_values($headers)[0]);
        } catch (\Exception $e) {
            $headers = $e->getHeaders();
            $session = new SessionHelper($request);
            $session->set('error', $e->getMessage());
            return $this->response->withStatus($e->getCode());
        }
    }
}

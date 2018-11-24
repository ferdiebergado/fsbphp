<?php

namespace FSB\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;
use App\View\Template\ViewTrait;

class ExceptionHandlerMiddleware implements MiddlewareInterface
{
    use ViewTrait;

    protected $viewfile = "layouts/errors";
    protected $template;
    protected $response;

    public function __construct(ContainerInterface $container)
    {
        $this->template = $container->get('template');
        $this->response = ($container->get('psr17factory'))->createResponse();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $view = $this->view($this->viewfile, array());
        $headers = $this->exception->getHeaders();
        if (null !== $headers && is_array($headers)) {
            foreach ($headers as $key => $value) {
                $this->response = $this->response->withHeader($key, $value);
            }
        }
        return $this->response->withStatus($this->exception->getStatusCode());
    }
}

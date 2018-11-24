<?php

namespace FSB\Middleware;

use FSB\Session\SessionHelper;
use Psr\Http\Message \{
    ResponseInterface, ServerRequestInterface, ResponseFactoryInterface
};
use Psr\Http\Server \{
    MiddlewareInterface, RequestHandlerInterface
};

class AuthMiddleware extends Middleware implements MiddlewareInterface
{
    protected $statusCode = '401';
    protected $redirectPath = '/login';

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        parent::__construct($responseFactory);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $response = $handler->handle($request);
        $session = new SessionHelper($request);
        $user = $session->get('user');
        if (null === $user) {
            return $response->withStatus($this->statusCode)->withHeader('Location', $this->redirectPath);
        }

        return $response;
    }
}

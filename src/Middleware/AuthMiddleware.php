<?php

namespace FSB\Middleware;

use Psr\Http\Message \{
    ResponseInterface, ServerRequestInterface, ResponseFactoryInterface
};
use Psr\Http\Server \{
    MiddlewareInterface, RequestHandlerInterface
};
use FSB\Session\Session;

class AuthMiddleware extends Middleware implements MiddlewareInterface
{
    protected $statusCode = '401';
    protected $redirectPath = '/login';
    protected $session;

    public function __construct(ResponseFactoryInterface $responseFactory, Session $session)
    {
        parent::__construct($responseFactory);
        $this->session = $session;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $response = $handler->handle($request);
        $user = $this->session->get('user');
        if (null === $user) {
            $this->session->set('REDIRECT_PATH', $request->getUri()->getPath());
            return $response->withStatus($this->statusCode)->withHeader('Location', $this->redirectPath);
        }

        return $response;
    }
}

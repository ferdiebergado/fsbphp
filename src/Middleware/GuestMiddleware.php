<?php

namespace FSB\Middleware;

use FSB\Session\SessionHelper;
use Psr\Http\Message \{
    ResponseInterface, ServerRequestInterface, ResponseFactoryInterface
};
use Psr\Http\Server \{
    MiddlewareInterface, RequestHandlerInterface
};

class GuestMiddleware extends Middleware implements MiddlewareInterface
{
    protected $statusCode = '301';
    protected $redirectPath = '/';

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        parent::__construct($responseFactory);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $session = new SessionHelper($request);
        $user = $session->get('user');
        if (null !== $user) {
            return $this->response->withStatus($this->statusCode)->withHeader('Location', $this->redirectPath);
        }

        // $request = $request->withAttribute('session', $session->getSession());

        return $handler->handle($request);
    }
}

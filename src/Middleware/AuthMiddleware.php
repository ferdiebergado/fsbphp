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
    protected $redirectPath = 'login';
    protected $guarded;

    public function __construct(ResponseFactoryInterface $responseFactory, $guarded = [])
    {
        parent::__construct($responseFactory);
        $this->guarded = $guarded;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $base_uri = explode('/', $request->getUri()->getPath());
        $uri = $base_uri[1];

        if (in_array($uri, $this->guarded)) {
            $session = new SessionHelper($request);
            $user = $session->get('user');
            if (null === $user) {
                $redirectPath = '/' . $this->redirectPath;
                $statuscode = $this->statusCode;
                $this->response->withStatus($statuscode);
                return $this->response->withHeader('Location', $redirectPath);
            }
        }

        // if ($uri === $this->redirectPath && null !== $user) {
        //     return $this->response->withHeader('Location', '/');
        // }

        return $handler->handle($request);
    }
}

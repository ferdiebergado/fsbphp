<?php

namespace FSB\Middleware;

use Psr\Http\Message \{
    ResponseInterface, ServerRequestInterface
};
use Psr\Http\Server \{
    MiddlewareInterface, RequestHandlerInterface
};

class SetRequestAttributesMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $session = $request->getAttribute('session');
        $segment = $session->getSegment('FSB');
        $segment->keepFlash();
        $user = $segment->get('user');
        $request = $request->withAttribute('user', $user);
        $request = $request->withAttribute('segment', $segment);
        return $handler->handle($request);
    }
}

<?php

namespace FSB\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class SanitizeInputMiddleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $post = $request->getParsedBody();
        foreach ($post as $key => $value) {
            $post[$key] = test_input($post[$key]);
            $post[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);
        }
        $request = $request->withParsedBody($post);
        return $handler->handle($request);
    }
}

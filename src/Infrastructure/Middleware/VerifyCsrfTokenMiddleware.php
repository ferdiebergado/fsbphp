<?php

namespace Bergado\Infrastructure\Middleware;

use Psr\Http\Server \{
    MiddlewareInterface, RequestHandlerInterface
};
use Psr\Http\Message \{
    ResponseInterface, ServerRequestInterface
};
use Zend\Diactoros\Response\RedirectResponse;
use function Bergado\Infrastructure\Functions\test_input;

class VerifyCsrfTokenMiddleware implements MiddlewareInterface
{
    protected $methods = [
        'POST',
        'PUT',
        'PATCH',
        'DELETE'
    ];
    protected $error = "Token Mismatch.";

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        if (in_array($request->getMethod(), $this->methods)) {
            $post = $request->getParsedBody();
            foreach ($post as $key => $value) {
                $post[$key] = test_input($post[$key]);
                $post[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);
            }
            $request = $request->withParsedBody($post);
            if (array_key_exists('__csrf_value', $post)) {
                $csrf_value = $post['__csrf_value'];
                $session = $request->getAttribute('session');
                $csrf_token = $session->getCsrfToken();
                if ($csrf_token->isValid($csrf_value)) {
                    return $handler->handle($request);
                }
            }
            $segment = $request->getAttribute('segment');
            $segment->setFlash('old', $post);
            $segment->setFlash('error', $this->error);
            $redirectPath = $request->getUri()->getPath();
            return new RedirectResponse($redirectPath);
        }
        return $handler->handle($request);
    }
}

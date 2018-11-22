<?php

namespace FSB\Middleware;

use Psr\Http\Server \{
    MiddlewareInterface, RequestHandlerInterface
};
use Psr\Http\Message \{
    ResponseInterface, ServerRequestInterface
};
use FSB\Session\SessionHelper;

class VerifyCsrfTokenMiddleware extends Middleware implements MiddlewareInterface
{
    protected $unsafe = [
        'POST',
        'PUT',
        'DELETE'
    ];
    protected $csrf_error = "Token Mismatch.";
    protected $csrf_statuscode = "401";

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $method = $request->getMethod();
        if (in_array($method, $this->unsafe)) {
            $body = $request->getParsedBody();
            $session = new SessionHelper($request);
            if (array_key_exists('__csrf_value', $body)) {
                $csrf_value = $body['__csrf_value'];
                $csrf_token = $session->getSession()->getCsrfToken();
                if ($csrf_token->isValid($csrf_value)) {
                    return $handler->handle($request);
                }
            }
            $session->flash('error', $this->csrf_error);
            return $this->response->withStatus($this->csrf_statuscode)->withHeader('Location', $request->getUri()->getPath());
        }
        return $handler->handle($request);
    }
}

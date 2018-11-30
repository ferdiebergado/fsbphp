<?php

namespace FSB\Middleware\Matcher;

use Middleland\Matchers\MatcherInterface;
use Psr\Http\Message\ServerRequestInterface;

class Method implements MatcherInterface
{
    private $methods = [
        'POST',
        'PUT',
        'PATCH',
        'DELETE'
    ];

    public function __invoke(ServerRequestInterface $request) : bool
    {
        return in_array($request->getMethod(), $this->methods);
    }
}

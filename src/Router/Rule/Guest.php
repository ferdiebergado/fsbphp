<?php

namespace FSB\Router\Rule;

use Aura\Router\Route;
use Aura\Router\Rule\RuleInterface;
use Psr\Http\Message\ServerRequestInterface;
use FSB\Session\SessionHelper;

class Guest implements RuleInterface
{
    public function __invoke(ServerRequestInterface $request, Route $route)
    {
        if (isset($route->auth['guest'])) {
            if ($route->auth['guest'] === true) {
                $session = new SessionHelper($request);
                if (null !== $session->get('user')) {
                    return false;
                }
            }
        }
        return true;
    }
}

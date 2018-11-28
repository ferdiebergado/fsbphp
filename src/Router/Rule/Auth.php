<?php

namespace FSB\Router\Rule;

use Aura\Router\Route;
use Aura\Router\Rule\RuleInterface;
use Psr\Http\Message\ServerRequestInterface;
use FSB\Session\SessionHelper;

class Auth implements RuleInterface
{
    public function __invoke(ServerRequestInterface $request, Route $route)
    {
        if ($route->auth['loggedIn']) {
            $session = new SessionHelper($request);
            if (null !== $session->get('user')) {
                return true;
            }
            return false;
        }
        return true;
    }
}

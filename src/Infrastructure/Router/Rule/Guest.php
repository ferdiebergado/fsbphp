<?php

namespace Bergado\Infrastructure\Router\Rule;

use Aura\Router\Route;
use Aura\Router\Rule\RuleInterface;
use Psr\Http\Message\ServerRequestInterface;

class Guest implements RuleInterface
{
    public function __invoke(ServerRequestInterface $request, Route $route)
    {
        if (isset($route->auth['loggedIn'])) {
            if (!$route->auth['loggedIn']) {
                $user = $request->getAttribute('user');
                if (null !== $user) {
                    return false;
                }
            }
        }
        return true;
    }
}
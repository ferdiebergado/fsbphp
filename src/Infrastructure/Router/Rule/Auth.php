<?php

namespace Bergado\Infrastructure\Router\Rule;

use Aura\Router\Route;
use Aura\Router\Rule\RuleInterface;
use Psr\Http\Message\ServerRequestInterface;

class Auth implements RuleInterface
{
    public function __invoke(ServerRequestInterface $request, Route $route)
    {
        if (isset($route->auth['loggedIn'])) {
            if ($route->auth['loggedIn']) {
                $user = $request->getAttribute('user');
                if (null === $user) {
                    $redirectPath = $request->getUri()->getPath();
                    $segment = $request->getAttribute('segment');
                    $segment->set('REDIRECT_PATH', $redirectPath);
                    return false;
                }
            }
        }
        return true;
    }
}

<?php

namespace FSB\Middleware;

use FSB\Session\SessionHelper;
use Psr\Http\Message \{
    ResponseInterface, ServerRequestInterface, ResponseFactoryInterface
};
use Psr\Http\Server \{
    MiddlewareInterface, RequestHandlerInterface
};
use Middlewares\AuraRouter;
use Zend\Diactoros\Response\RedirectResponse;

class RouterMiddleware extends AuraRouter
{
    /**
     * @var RouterContainer The router container
     */
    private $router;

    /**
     * @var string Attribute name for handler reference
     */
    private $attribute = 'request-handler';

    public function __construct(\Aura\Router\RouterContainer $router)
    {
        parent::__construct($router);
    }

    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $matcher = $this->router->getMatcher();
        $route = $matcher->match($request);

        if (!$route) {
            $failedRoute = $matcher->getFailedRoute();

            switch ($failedRoute->failedRule) {
                case 'Aura\Router\Rule\Allows':
                    return $this->createResponse(405)
                        ->withHeader('Allow', implode(', ', $failedRoute->allows)); // 405 METHOD NOT ALLOWED
                case 'Aura\Router\Rule\Accepts':
                    return $this->createResponse(406); // 406 NOT ACCEPTABLE
                case 'Aura\Router\Rule\Host':
                case 'Aura\Router\Rule\Path':
                    return $this->createResponse(404); // 404 NOT FOUND
                case 'FSB\Router\Rule\Auth':
                    return new RedirectResponse('/login', 403);
                default:
                    return $this->createResponse(500); // 500 INTERNAL SERVER ERROR
            }
        }

        foreach ($route->attributes as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        $request = $request->withAttribute($this->attribute, $route->handler);

        return $handler->handle($request);
    }
}

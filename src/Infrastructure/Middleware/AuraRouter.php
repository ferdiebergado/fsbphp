<?php
declare (strict_types = 1);

namespace Bergado\Infrastructure\Middleware;

use Aura\Router\RouterContainer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\RedirectResponse;
use Middlewares\Utils\Traits\HasResponseFactory;
use Middlewares\HttpErrorException;

class AuraRouter implements MiddlewareInterface
{
    use HasResponseFactory;

    /**
     * @var RouterContainer The router container
     */
    private $router;

    /**
     * @var string Attribute name for handler reference
     */
    private $attribute = 'request-handler';

    /**
     * Set the RouterContainer instance.
     */
    public function __construct(RouterContainer $router)
    {
        $this->router = $router;
    }

    /**
     * Set the attribute name to store handler reference.
     */
    public function attribute(string $attribute) : self
    {
        $this->attribute = $attribute;
        return $this;
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
                    throw HttpErrorException::create(405, [
                        'request' => $request,
                        'headers' => [
                            'Allow' => implode(', ', $failedRoute->allows)
                        ]
                    ]);
                    // return $this->createResponse(405)
                    //     ->withHeader('Allow', implode(', ', $failedRoute->allows)); // 405 METHOD NOT ALLOWED
                case 'Aura\Router\Rule\Accepts':
                    throw HttpErrorException::create(406, [
                        'request' => $request,
                        'headers' => [
                            'Accept' => implode(', ', $failedRoute->accepts)
                        ]
                    ]);                
                    // return $this->createResponse(406); // 406 NOT ACCEPTABLE
                case 'Aura\Router\Rule\Host':
                case 'Aura\Router\Rule\Path':
                    throw HttpErrorException::create(404, [
                        'request' => $request
                    ]);                   
                    // return $this->createResponse(404); // 404 NOT FOUND
                case 'Bergado\Infrastructure\Router\Rule\Guest':
                    return new RedirectResponse('/');
                case 'Bergado\Infrastructure\Router\Rule\Auth':
                    return new RedirectResponse('/login');
                default:
                    throw HttpErrorException::create(500, [
                        'request' => $request
                    ]);                                   
                    // return $this->createResponse(500); // 500 INTERNAL SERVER ERROR
            }
        }

        foreach ($route->attributes as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        $request = $request->withAttribute($this->attribute, $route->handler);

        return $handler->handle($request);
    }
}

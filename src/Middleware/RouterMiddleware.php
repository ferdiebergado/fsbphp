<?php
declare (strict_types = 1);

namespace FSB\Middleware;

use Middlewares\Utils\Factory;
use Middlewares\Utils\Traits\HasResponseFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use League\Route\Router;
use League\Route\Http\Exception\MethodNotAllowedException;
use League\Route\Http\Exception\NotFoundException;

class RouterMiddleware implements MiddlewareInterface
{
    use HasResponseFactory;

    /**
     * @var Router FSB Router
     */
    private $router;

    /**
     * @var string Attribute name for handler reference
     */
    private $attribute = 'request-handler';

    /**
     * Set the Dispatcher instance and optionally the response factory to return the error responses.
     */
    public function __construct(Router $router, ResponseFactoryInterface $responseFactory = null)
    {
        $this->router = $router;
        $this->responseFactory = $responseFactory ? : Factory::getResponseFactory();
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
        try {
            $response = $this->router->dispatch($request);
            return $response;
        } catch (NotFoundException $e) {
            return $this->createResponse(404);
        } catch (MethodNotAllowedException $e) {
            $headers = $e->getHeaders();
            $keys = array_keys($headers);
            $values = array_values($headers);
            return $this->createResponse(405)->withHeader($keys[0], $values[0]);
        }

        // if ($route[0] === Dispatcher::NOT_FOUND) {
        // }

        // if ($route[0] === Dispatcher::METHOD_NOT_ALLOWED) {
        // }

        // foreach ($route[2] as $name => $value) {
        //     $request = $request->withAttribute($name, $value);
        // }

        // $request = $this->setHandler($request, $route[1]);

        // return $handler->handle($request);
    }

    /**
     * Set the handler reference on the request.
     *
     * @param mixed $handler
     */
    // protected function setHandler(ServerRequestInterface $request, $handler): ServerRequestInterface
    // {
    //     return $request->withAttribute($this->attribute, $handler);
    // }
}

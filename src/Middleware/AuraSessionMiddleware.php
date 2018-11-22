<?php
declare (strict_types = 1);

namespace FSB\Middleware;

use Aura\Session\SessionFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuraSessionMiddleware implements MiddlewareInterface
{
    /**
     * @var SessionFactory
     */
    private $factory;

    /**
     * @var string|null The session name
     */
    private $name;

    /**
     * @var string The attribute name
     */
    private $attribute = 'session';

    protected $config;

    /**
     * Set the session factory.
     *
     * @param SessionFactory|null $factory
     */
    public function __construct(SessionFactory $factory = null, $config = [])
    {
        $this->factory = $factory;
        $this->config = $config;
    }

    /**
     * Set the session name.
     */
    public function name(string $name) : self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the attribute name to store the sesion instance.
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
        $factory = $this->factory ? : new SessionFactory();
        $session = $factory->newInstance($request->getCookieParams());

        if ($this->name !== null) {
            $session->setName($this->name);
        }

        if ($this->config !== null) {
            $session->setSavePath($this->config['save_path']);
            $session->setCookieParams($this->config['cookie']);
        }

        $request = $request->withAttribute($this->attribute, $session);

        return $handler->handle($request);
    }
}

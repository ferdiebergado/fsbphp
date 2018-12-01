<?php
declare (strict_types = 1);

namespace FSB\Middleware;

use Aura\Session\SessionFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Noodlehaus\Config;

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
    public function __construct(SessionFactory $factory = null, Config $config)
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

        $session->setName($this->config->get('session.name'));

        if ($this->name !== null) {
            $session->setName($this->name);
        }

        if ($this->config !== null) {
            $session->setSavePath($this->config->get('session.save_path'));
            $session->setCookieParams($this->config->get('session.cookie'));
        }

        $segment = $session->getSegment($this->config->get('session.segment'));

        $server = $request->getServerParams();
        $userIp = $request->getAttribute('client-ip');
        $userAgent = $server['HTTP_USER_AGENT'];
        $ssl = $server['REQUEST_SCHEME'] === 'https' ? true : false;
        
        // prevent session hijacking
        if ($segment->get('IPaddress') != $userIp || $segment->get('userAgent') != $userAgent) {
            $session->clear();
            $session->destroy();
            $segment = $session->getSegment('FSB');
            $segment->set('IPaddress', $userIp);
            $segment->set('userAgent', $userAgent);
            $segment->set('isSsl', $ssl);
            $session->regenerateId();
        }

        // regenerate session id and set cookie secure flag when switching between http and https
        if ($segment->get('isSsl') !== $ssl) {
            $segment->set('isSsl', $ssl);
            $session->setCookieParams(['secure' => $ssl]);
            $session->regenerateId();
        }

        // record session activity
        if (!$segment->get('start_time')) {
            $segment->set('start_time', time());
        }
        $segment->set('last_activity', time());

        // delete session expired also server side
        if ($segment->get('start_time') < (strtotime('-1 hours')) || $segment->get('last_activity') < (strtotime('-20 mins'))) {
            $session->clear();
            $session->destroy();
        }

        $segment->keepFlash();
        $user = $segment->get('user');
        $request = $request->withAttribute('user', $user);
        $request = $request->withAttribute('segment', $segment);
        $request = $request->withAttribute('user-agent', $userAgent);
        $request = $request->withAttribute('ssl', $ssl);
        $request = $request->withAttribute($this->attribute, $session);

        return $handler->handle($request);
    }
}

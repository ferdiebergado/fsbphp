<?php declare (strict_types = 1);

namespace FSB\Session;

use Aura\Session\SessionFactory;
use Aura\Session\Session as AuraSession;
use Aura\Session\Segment;
use Psr\Http\Message\ServerRequestInterface;

class Session
{
    protected $segmentname = 'FSB';
    protected $sessionFactory;
    protected $session;
    protected $segment;
    protected $csrf;
    protected $config;
    protected $request;

    public function __construct(SessionFactory $sessionFactory, ServerRequestInterface $request, array $config)
    {
        $this->sessionFactory = $sessionFactory;
        $this->request = $request;
        $this->config = $config;
        $this->setSession();
        $this->setSegment();
        $this->setCsrf();
    }

    protected function setSession() : self
    {
        $cookie = $this->request->getCookieParams();
        $this->session = $this->sessionFactory->newInstance($cookie);
        if (!$this->session->isStarted()) {
            $this->session->setName($this->config['name']);
            $this->session->setSavePath($this->config['save_path']);
            $this->session->setCookieParams($this->config['cookie']);
        }
        return $this;
    }

    protected function setSegment() : self
    {
        $this->segment = $this->session->getSegment($this->segmentname);
        return $this;
    }

    public function getSession() : AuraSession
    {
        return $this->session;
    }

    public function getSegment() : Segment
    {
        return $this->segment;
    }

    public function setCsrf() : self
    {
        $this->csrf = $this->session->getCsrfToken()->getValue();
        return $this;
    }

    public function getCsrf() : string
    {
        return $this->csrf;
    }

    public function getCsrfField() : string
    {
        return '<input type="hidden" name="__csrf_value" value="'
            . htmlspecialchars($this->csrf, ENT_QUOTES, 'UTF-8')
            . '"></input>';
    }

    public function flash(string $key, $value) : void
    {
        $this->segment->setFlash($key, $value);
    }

    public function flashNow(string $key, $value) : void
    {
        $this->segment->setFlashNow($key, $value);
    }

    public function set(string $key, $value) : self
    {
        $this->segment->set($key, $value);
        return $this;
    }

    public function get(string $key, $default = null)
    {
        return $this->segment->get($key, $default);
    }

    public function setOldFields() : self
    {
        $this->old = $this->segment->set('old', $this->body);
        return $this;
    }

    public function old(string $key) : string
    {
        return $this->old[$key];
    }

    public function getFlash($key, $alt = null)
    {
        return $this->segment->getFlash($key, $alt);
    }

    public function getFlashNext($key, $alt = null)
    {
        return $this->segment->getFlashNext($key, $alt);
    }

    public function keepFlash() : void
    {
        $this->segment->keepFlash();
    }

    public function __call($method, $args) : void
    {
        $this->session->$method($args);
    }
}

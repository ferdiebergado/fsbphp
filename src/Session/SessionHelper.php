<?php declare (strict_types = 1);

namespace FSB\Session;

use Psr\Http\Message\ServerRequestInterface;
use Aura\Session\Session;
use Aura\Session\Segment;

class SessionHelper
{
    protected $sessionHandler = 'session';
    protected $segmentName = 'FSB';
    protected $session;
    protected $segment;
    protected $request;
    public $csrf;
    protected $body;
    protected $old;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
        $this->body = $request->getParsedBody();
        $this->setSession();
        $this->setSegment();
        $this->setCsrf();
        $this->setOldFields();
    }

    protected function setSession() : self
    {
        $this->session = $this->request->getAttribute($this->sessionHandler);
        return $this;
    }

    public function getSession() : Session
    {
        return $this->session;
    }

    protected function setSegment() : self
    {
        $this->segment = $this->session->getSegment($this->segmentName);
        return $this;
    }

    public function getSegment() : Segment
    {
        return $this->segment;
    }

    public function flash(string $key, $value) : void
    {
        $this->segment->setFlash($key, $value);
    }

    public function flashNow(string $key, $value) : void
    {
        $this->segment->setFlashNow($key, $value);
    }

    public function setCsrf() : self
    {
        $this->csrf = $this->session->getCsrfToken()->getValue();
        return $this;
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

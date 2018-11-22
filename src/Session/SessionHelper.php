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

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
        $this->setSession();
        $this->setSegment();
        $this->setCsrf();
    }

    protected function setSession() : self
    {
        $this->session = $this->request->getAttribute($this->sessionHandler);
        // $fsbsession = $this->request->getAttribute('fsbsession');
        // if (isset($fsbsession)) {
        //     $this->session = $fsbsession;
        // }
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

    public function __call($method, $args) : void
    {
        $this->session->$method($args);
    }
}

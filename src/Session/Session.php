<?php declare (strict_types = 1);

namespace FSB\Session;

use Aura\Session\SessionFactory;
use Aura\Session\Session as AuraSession;
use Aura\Session\Segment;

class Session
{
    protected $segmentname = 'FSB';
    protected $sessionFactory;
    protected $session;
    protected $segment;
    protected $csrf;
    protected $config;

    public function __construct(SessionFactory $sessionFactory, array $config)
    {
        $this->sessionFactory = $sessionFactory;
        $this->config = $config;
        $this->setSession();
        $this->setSegment();
        $this->setCsrf();
    }

    protected function setSession() : self
    {
        $this->session = $this->sessionFactory->newInstance($_COOKIE);
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
}

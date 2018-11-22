<?php

namespace App\View\Template\Twig\Extension;

use FSB\Session\Session;

class AppTwigExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    protected $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function getGlobals()
    {
        return array(
            'session' => $this->session->getSegment(),
            'csrf' => $this->session->getCsrfField(),
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_Function('mem_usage', array($this, 'get_mem_usage')),
            new \Twig_Function('convert', array($this, 'convertSize')),
            new \Twig_Function('responsetime', array($this, 'getResponseTime')),
        );
    }

    public function get_mem_usage($value)
    {
        return memory_get_usage($value);
    }

    public function convertSize($size)
    {
        return convert($size);
    }

    public function getResponseTime()
    {
        return sprintf('%2.3f', (microtime(true) - FSB_TIME) * 1000);
    }
}

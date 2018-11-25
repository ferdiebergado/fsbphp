<?php

namespace App\View\Template\Twig\Extension;

use FSB\Session\Session;
use DebugBar\StandardDebugBar;
use DebugBar\Bridge\Twig\TraceableTwigEnvironment;
use Psr\Http\Message\ServerRequestInterface;

class FSBTwigExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    protected $session;
    protected $request;

    public function __construct(Session $session, ServerRequestInterface $request)
    {
        $this->session = $session;
        $this->request = $request;
    }

    public function getGlobals()
    {
        $globals = [
            'session' => $this->session->getSegment(),
            'csrf' => $this->session->getCsrfField(),
        ];

        if (DEBUG_MODE) {
            $debugbar = new \DebugBar\StandardDebugBar();
            $debugbarRenderer = $debugbar->getJavascriptRenderer();
            $debugbarRenderer->setBaseUrl('/debugbar');
            // $loader = new \Twig_Loader_Filesystem(VIEW_PATH);
            // $view = require(CONFIG_PATH . 'view.php');
            // $env = new \DebugBar\Bridge\Twig\TraceableTwigEnvironment(new \Twig_Environment($loader));
            // $debugbar->addCollector(new \DebugBar\Bridge\Twig\TwigCollector($env));
            $globals = array_merge($globals, ['debugbar' => $debugbarRenderer]);
        }

        return $globals;
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

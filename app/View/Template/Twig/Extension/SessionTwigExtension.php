<?php

namespace App\View\Template\Twig\Extension;

use Zend\Expressive\Session\Session;

class SessionTwigExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    protected $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function getGlobals()
    {
        return array(
            'session' => $this->session,
            'csrf' => '',
        );
    }
}

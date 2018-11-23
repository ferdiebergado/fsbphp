<?php

namespace App\Command;

use FSB\Session\SessionHelper;
use Zend\Expressive\Session\SessionInterface;

class LogoutCommand
{
    public $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }
}

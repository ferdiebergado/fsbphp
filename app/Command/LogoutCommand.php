<?php

namespace App\Command;

use FSB\Session\SessionHelper;

class LogoutCommand
{
    public $session;

    public function __construct(SessionHelper $session)
    {
        $this->session = $session;
    }
}

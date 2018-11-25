<?php

namespace App\Command;

use FSB\Session\Session;

class LogoutCommand
{
    public $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }
}

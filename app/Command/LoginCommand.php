<?php

namespace App\Command;

use FSB\Session\SessionHelper;

class LoginCommand
{
    public $session;
    public $body;

    public function __construct(SessionHelper $session, array $body)
    {
        $this->session = $session;
        $this->body = $body;
    }
}

<?php

namespace App\Command;

use FSB\Session\Session;

class LoginCommand
{
    public $session;
    public $body;

    public function __construct(Session $session, array $body)
    {
        $this->session = $session;
        $this->body = $body;
    }
}

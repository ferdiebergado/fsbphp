<?php

namespace App\Command;

use Aura\Session\Session;
use Aura\Session\Segment;

class LoginCommand
{
    public $session;
    public $segment;
    public $body;

    public function __construct(Session $session, Segment $segment, array $body)
    {
        $this->session = $session;
        $this->segment = $segment;
        $this->body = $body;
    }
}

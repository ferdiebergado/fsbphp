<?php

namespace App\Command;

use Aura\Session\Session;
use Aura\Session\Segment;

class LoginCommand
{
    public $session;
    public $segment;
    public $ip;
    public $userAgent;
    public $ssl;
    public $body;

    public function __construct(Session $session, Segment $segment, string $ip, string $userAgent, bool $ssl, $body = [])
    {
        $this->session = $session;
        $this->segment = $segment;
        $this->ip = $ip;
        $this->userAgent = $userAgent;
        $this->ssl = $ssl;
        $this->body = $body;
    }
}

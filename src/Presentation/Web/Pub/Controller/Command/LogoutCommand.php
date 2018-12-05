<?php

namespace Bergado\Presentation\Web\Pub\Controller\Command;

use Aura\Session\Session;

class LogoutCommand
{
    public $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }
}

<?php

namespace Bergado\Presentation\Web\Pub\Controller\Handler;

use Bergado\Presentation\Web\Pub\Controller\Command\LogoutCommand;

class LogoutHandler
{
    public function handle(LogoutCommand $logout)
    {
        $logout->session->clear();
        $logout->session->destroy();
    }
}

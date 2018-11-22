<?php

namespace App\Handler;

use App\Command\LogoutCommand;

class LogoutHandler
{
    public function handle(LogoutCommand $logout)
    {
        $logout->session->destroy();
    }
}

<?php

use App\Command\LogoutCommand;
use App\Handler\LogoutHandler;
use App\Command\LoginCommand;
use App\Handler\LoginHandler;

return [
    LogoutCommand::class => LogoutHandler::class,
    LoginCommand::class => LoginHandler::class,
];

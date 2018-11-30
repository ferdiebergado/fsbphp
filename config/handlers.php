<?php

/**
 * COMMAND BUS HANDLERS MAP
 * @return array
 */

use App\Command\LoginCommand;
use App\Handler\LoginHandler;
use App\Command\LogoutCommand;
use App\Handler\LogoutHandler;
use App\Command\UserShowCommand;
use App\Handler\UserShowHandler;

return [
    LogoutCommand::class => LogoutHandler::class,
    LoginCommand::class => LoginHandler::class,
    UserShowCommand::class => UserShowHandler::class
];

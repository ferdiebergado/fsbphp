<?php

/**
 * COMMAND BUS HANDLERS MAP
 * @return array
 */

// use App\Command\LoginCommand;
// use App\Handler\LoginHandler;
// use App\Command\LogoutCommand;
// use App\Handler\LogoutHandler;
// use App\Command\UserShowCommand;
// use App\Handler\UserShowHandler;

return [
    'handlers' => [
        \App\Command\LogoutCommand::class => \App\Handler\LogoutHandler::class,
        \App\Command\LoginCommand::class => \App\Handler\LoginHandler::class,
        \App\Command\UserShowCommand::class => \App\Handler\UserShowHandler::class
    ]
];

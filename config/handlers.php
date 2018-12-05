<?php

/**
 * COMMAND BUS HANDLERS MAP
 * @return array
 */

return [
    'handlers' => [
        \Bergado\Presentation\Web\Pub\Controller\Command\LogoutCommand::class => \Bergado\Presentation\Web\Pub\Controller\Handler\LogoutHandler::class,
        \Bergado\Presentation\Web\Pub\Controller\Command\LoginCommand::class => \Bergado\Presentation\Web\Pub\Controller\Handler\LoginHandler::class,
        \Bergado\Presentation\Web\Pub\Controller\Command\UserShowCommand::class => \Bergado\Presentation\Web\Pub\Controller\Handler\UserShowHandler::class
    ]
];

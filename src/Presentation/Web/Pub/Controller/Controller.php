<?php

namespace Bergado\Presentation\Web\Pub\Controller;

use Bergado\Core\Application\Service\TemplateInterface;
use Bergado\Infrastructure\Utility\ViewTrait;
use League\Tactician\CommandBus;

class Controller
{
    use ViewTrait;

    protected $template;
    protected $commandBus;

    public function __construct(TemplateInterface $template, CommandBus $commandBus)
    {
        $this->template = $template;
        $this->commandBus = $commandBus;
    }
}

<?php

namespace App\Controller;

use App\View\Template\TemplateInterface;
use App\View\Template\ViewTrait;
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

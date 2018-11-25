<?php

namespace App\Controller;

use Valitron\Validator;
use FSB\Session\Session;
use Aura\Filter\FilterFactory;
use App\View\Template\ViewTrait;
use League\Tactician\CommandBus;
use App\View\Template\TemplateInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class Controller
{
    use ViewTrait;

    protected $template;
    protected $response;
    protected $commandBus;
    protected $validator;
    protected $session;

    public function __construct(ResponseFactoryInterface $responseFactory, TemplateInterface $template, CommandBus $commandBus, Validator $validator, Session $session)
    {
        $this->response = $responseFactory->createResponse();
        $this->template = $template;
        $this->commandBus = $commandBus;
        $this->validator = $validator;
        $this->session = $session;
    }
}

<?php

namespace App\Controller;

use Psr\Http\Message\ResponseFactoryInterface;
use App\View\Template\TemplateInterface;
use App\View\Template\ViewTrait;
use League\Tactician\CommandBus;
use Aura\Filter\FilterFactory;
use Valitron\Validator;

class Controller
{
    use ViewTrait;

    protected $template;
    protected $response;
    protected $commandBus;
    protected $validator;

    public function __construct(ResponseFactoryInterface $responseFactory, TemplateInterface $template, CommandBus $commandBus, Validator $validator)
    {
        $this->response = $responseFactory->createResponse();
        $this->template = $template;
        $this->commandBus = $commandBus;
        $this->validator = $validator;
    }
}

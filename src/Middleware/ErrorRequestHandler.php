<?php

namespace FSB\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\View\Template\TemplateInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\Response\RedirectResponse;

class ErrorRequestHandler implements RequestHandlerInterface
{
    protected $viewfile = "layouts/errors";
    private $template;

    public function __construct(TemplateInterface $template)
    {
        $this->template = $template;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        //Get the error info as an instance of Middlewares\HttpErrorException
        $error = $request->getAttribute('error');

        //The error can contains context data that you can use, for example for PSR-3 loggin
        // Logger::error("There's an error", $error->getContext());

        $code = $error->getCode();
        $message = $error->getMessage();

        $data = [
            'error' => [
                'code' => $code,
                'message' => $message
            ]
        ];

        $context = $error->getContext();
        $requestContext = $context['request'];
        $headers = [];
        if (isset($context['headers'])) {
            $headers = $context['headers'];
        }

        switch ($requestContext->getHeaderLine('accept')) {
            case 'application/json':
                return new JsonResponse($data, $code, $headers);
            case 'text/html':
                //Any output is captured and added to the response's body
                $template = $this->template->render($this->viewfile . '.html.twig', $data['error']);
                return new HtmlResponse($template, $code, $headers);
            default:
                return new TextResponse($message, $code, $headers);
        }
        // return (new Response())->withStatus($error->getCode());
    }
}

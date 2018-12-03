<?php
declare (strict_types = 1);

namespace FSB\Middleware;

use Whoops\Run;
use Monolog\Logger;
use Noodlehaus\Config;
use Middlewares\HttpErrorException;
use Whoops\Handler\HandlerInterface;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\RedirectResponse;

class WhoopsMiddleware implements MiddlewareInterface
{
    private $whoops;
    private $prettypagehandler;
    private $plaintexthandler;
    private $logger;
    private $mailer;
    private $config;
    private $template;

    public function __construct(Run $whoops, HandlerInterface $prettypagehandler, HandlerInterface $plaintexthandler, Logger $logger, \Swift_Mailer $mailer, Config $config)
    {
        $this->whoops = $whoops;
        $this->prettypagehandler = $prettypagehandler;
        $this->plaintexthandler = $plaintexthandler;
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->config = $config;
    }

    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $this->whoops->register();
        if (DEBUG_MODE) {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
            $this->whoops->pushHandler($this->prettypagehandler);
        } else {
            $this->whoops->allowQuit(false);
            $this->whoops->writeToOutput(false);
            $plaintexthandler = $this->plaintexthandler;
            $logger = $this->logger;
            $this->whoops->pushHandler(function ($exception, $inspector, $whoops) use ($plaintexthandler, $logger) {
                $whoops->pushHandler($plaintexthandler);
                $body = $whoops->handleException($exception);
                $logger->error($body);
            });
            $prettypagehandler = $this->prettypagehandler;
            $mailer = $this->mailer;
            $config = $this->config;
            $this->whoops->pushHandler(function ($exception, $inspector, $whoops) use ($mailer, $prettypagehandler, $config) {
                $whoops->pushHandler($prettypagehandler);
                $body = $whoops->handleException($exception);
                $mail = $config['app.author'];
                $message = (new \Swift_Message('Exception Report'))
                    ->setFrom($mail['email'])
                    ->setTo($mail['email'])
                    ->setBody($body, 'text/html');
                $mailer->send($message);
                // return new RedirectResponse('/');
                $whoops->handleShutdown(function () {
                    throw HttpErrorException::create(500, [
                        'request' => $request
                    ]);
                });
            });
        }
        return $handler->handle($request);
    }
}

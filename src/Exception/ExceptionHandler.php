<?php

namespace FSB\Exception;

use Whoops\Run;
use Monolog\Logger;
use Noodlehaus\Config;
use Whoops\Handler\Handler;
use App\View\Template\ViewTrait;
use Whoops\Handler\HandlerInterface;
use Whoops\Handler\PrettyPageHandler;
use Monolog\Handler\SwiftMailerHandler;
use Whoops\Handler\JsonResponseHandler;
use App\View\Template\TemplateInterface;
use Zend\Diactoros\Response\RedirectResponse;
use Monolog\Handler\HandlerInterface as LogHandlerInterface;

/* Register the error handler */
class ExceptionHandler
{
    use ViewTrait;

    private $whoops;
    private $prettypagehandler;
    private $plaintexthandler;
    private $logger;
    private $mailer;
    private $config;
    private $template;

    public function __construct(Run $whoops, HandlerInterface $prettypagehandler, HandlerInterface $plaintexthandler, Logger $logger, \Swift_Mailer $mailer, Config $config, TemplateInterface $template)
    {
        $this->whoops = $whoops;
        $this->prettypagehandler = $prettypagehandler;
        $this->plaintexthandler = $plaintexthandler;
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->config = $config;
        $this->template = $template;
    }

    public function register()
    {
        $this->whoops->register();
        $this->whoops->pushHandler(new JsonResponseHandler());
        if (DEBUG_MODE) {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
            $this->whoops->pushHandler($this->prettypagehandler);
        } else {
            $this->whoops->allowQuit(false);
            $this->whoops->writeToOutput(false);
            $this->whoops->sendHttpCode(true);
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
                return;
                // header('Content-Type: text/html');
                // echo $this->template->render('layouts/errors.html.twig');
            });
        }
    }
}

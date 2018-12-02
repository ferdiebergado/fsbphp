<?php

namespace FSB\Exception;

use Whoops\Run;
use Monolog\Logger;
use Noodlehaus\Config;
use Whoops\Handler\HandlerInterface;
use Monolog\Handler\SwiftMailerHandler;
use Monolog\Handler\HandlerInterface as LogHandlerInterface;
use Whoops\Handler\PrettyPageHandler;
use App\View\Template\TemplateInterface;
use App\View\Template\ViewTrait;
use Zend\Diactoros\Response\RedirectResponse;

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
        if (DEBUG_MODE) {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
            $this->whoops->pushHandler($this->prettypagehandler);
        } else {
            $whoops = &$this->whoops;
            $whoops->allowQuit(false);
            $whoops->writeToOutput(false);
            $plaintexthandler = $this->plaintexthandler;
            $logger = $this->logger;

            $this->whoops->pushHandler(function ($e) use ($whoops, $plaintexthandler, $logger) {
                $whoops->pushHandler($plaintexthandler);
                $body = $whoops->handleException($e);
                $logger->error($body);
            });
            $prettypagehandler = $this->prettypagehandler;
            $mailer = $this->mailer;
            $config = $this->config;
            $this->whoops->pushHandler(function ($e) use ($whoops, $mailer, $prettypagehandler, $config) {
                $whoops->pushHandler($prettypagehandler);
                $body = $whoops->handleException($e);
                $mail = $config['app.author'];
                $message = (new \Swift_Message('Exception Report'))
                    ->setFrom($mail['email'])
                    ->setTo($mail['email'])
                    ->setBody($body, 'text/html');
                $mailer->send($message);
            });
        }
        $this->whoops->register();
    }
}

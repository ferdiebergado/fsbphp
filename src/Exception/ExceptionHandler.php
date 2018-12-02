<?php

namespace FSB\Exception;

use Whoops\Run;
use Whoops\Handler\HandlerInterface;

/* Register the error handler */
class ExceptionHandler
{
    private $whoops;
    private $prettyagehandler;
    private $plaintexthandler;

    public function __construct(Run $whoops, HandlerInterface $prettypagehandler, HandlerInterface $plaintexthandler)
    {
        $this->whoops = $whoops;
        $this->prettypagehandler = $prettypagehandler;
        $this->plaintexthandler = $plaintexthandler;
    }

    public function register()
    {
        if (DEBUG_MODE) {
            error_reporting(E_ALL);
            $this->whoops->pushHandler($this->prettypagehandler);
        } else {
            $this->whoops->pushHandler($this->plaintexthandler);
    // $whoops->pushHandler(function ($e) use ($whoops, $plaintexthandler) {
        // $whoops->allowQuit(false);
        // $whoops->writeToOutput(false);
        // $whoops->pushHandler($prettyagehandler);
        // $body = $whoops->handleException($e);
        // $whoops->pushHandler($plaintexthandler);
        // $app = require(CONFIG_PATH . 'app.php');
        // Core\Mail::send($app['author_email'], $app['name'] . ' Error Exception', $body);
        // logger($e->getMessage(), 2);
        // require VIEW_PATH . 'errors/500.php';
    // });
        }
        $this->whoops->register();
    }
}

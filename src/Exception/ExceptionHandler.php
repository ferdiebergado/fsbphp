<?php

namespace FSB\Exception;

use Whoops\Handler\PrettyPageHandler;

/* Register the error handler */
class ExceptionHandler
{
    public function __invoke()
    {
        $whoops = new Whoops\Run;

        $pagehandler = new PrettyPageHandler;

        if (DEBUG_MODE) {
            error_reporting(E_ALL);
            $whoops->pushHandler($pagehandler);
        } else {
            $whoops->pushHandler(function ($e) use ($whoops, $pagehandler) {
                $whoops->allowQuit(false);
                $whoops->writeToOutput(false);
                $whoops->pushHandler($pagehandler);
                $body = $whoops->handleException($e);
        // $app = require(CONFIG_PATH . 'app.php');
        // Core\Mail::send($app['author_email'], $app['name'] . ' Error Exception', $body);
                logger($e->getMessage(), 2);
        // require VIEW_PATH . '500.php';
            });
        }
        $whoops->register();
    }
}

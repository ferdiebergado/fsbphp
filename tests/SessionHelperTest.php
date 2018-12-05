<?php

use PHPUnit\Framework\TestCase;
use Bergado\Container;

class SessionHelperTest extends TestCase
{
    public function sessionHandlerCanBeRetrievedFromRequest()
    {
        $container = new Container();
        $request = $container->get('request');

    }

}

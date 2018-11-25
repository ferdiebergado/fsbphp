<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use duncan3dc\Laravel\Dusk;

class LoginTest extends TestCase
{
    public function testLoginRedirectsToIntendedUrl()
    {
        require __DIR__ . "/../vendor/autoload.php";
        $dusk = new Dusk();
        // $browser = $dusk->getBrowser();
        $dusk->setBaseUrl("http://localhost:1978");
        $dusk->visit("/login");
    }
}

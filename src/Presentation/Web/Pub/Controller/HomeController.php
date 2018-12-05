<?php declare (strict_types = 1);

namespace Bergado\Presentation\Web\Pub\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HomeController extends Controller
{
    public function index(ServerRequestInterface $request) : ResponseInterface
    {
        $data = "Welcome!";
        return $this->view("home", compact('data'));
    }
}

<?php

namespace App\Controller;

use App\Model\User;
use Psr\Http\Message \{
    ResponseInterface, ServerRequestInterface
};
use App\Command\UserShowCommand;

class UserController extends Controller
{
    public function read(ServerRequestInterface $request) : ResponseInterface
    {
        $id = (int)$request->getAttribute('id');
        $show = new UserShowCommand($id);
        $data = $this->commandBus->handle($show);
        return $this->view("home", compact('data'));
    }
}

<?php

namespace App\Controller;

use App\Model\User;
use Psr\Http\Message \{
    ResponseInterface, ServerRequestInterface
};

class UserController extends Controller
{
    public function show(ServerRequestInterface $request) : ResponseInterface
    {
        $user = (int)$request->getAttribute('user');
        $data = User::find($user);
        return $this->view("home", compact('data'));
    }
}

<?php

namespace App\Controller;

use App\Model\User;
use Psr\Http\Message \{
    ResponseInterface, ServerRequestInterface
};

class UserController extends Controller
{
    public function show(ServerRequestInterface $request, array $params) : ResponseInterface
    {
        $data = User::find($params['user']);
        return $this->view("home", compact('data'));
    }
}

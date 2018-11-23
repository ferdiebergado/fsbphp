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
        $session = $request->getAttribute('session');
        $this->template->addGlobal('session', $session);
        return $this->view("home", compact('data'));
    }
}

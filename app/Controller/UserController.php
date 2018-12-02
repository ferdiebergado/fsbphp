<?php

namespace App\Controller;

use App\Model\User;
use Psr\Http\Message \{
    ResponseInterface, ServerRequestInterface
};
use App\Command\UserShowCommand;

class UserController extends Controller
{
    public function browse(ServerRequestInterface $request) : ResponseInterface
    {
        // $browseUsers = new UserBrowseCommand();
        $users = User::paginate(10);

        $data = json_encode($users->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return $this->view('home', compact('data'));
    }

    public function read(ServerRequestInterface $request) : ResponseInterface
    {
        $id = (int)$request->getAttribute('id');
        $show = new UserShowCommand($id);
        $data = $this->commandBus->handle($show);
        return $this->view("home", compact('data'));
    }
}

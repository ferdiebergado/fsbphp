<?php

namespace Bergado\Presentation\Web\Pub\Controller;

use Bergado\Domain\Entity\User;
use Psr\Http\Message \{
    ResponseInterface, ServerRequestInterface
};
use Bergado\Presentation\Web\Pub\Controller\Command\UserShowCommand;

class UserController extends Controller
{
    public function browse(ServerRequestInterface $request) : ResponseInterface
    {
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

    public function edit(ServerRequestInterface $request) : ResponseInterface
    {
        $id = (int)$request->getAttribute('id');
        $user = User::find($id);
        $data = $user->toArray();
        return $this->view('users/edit', compact('data'));
    }
}

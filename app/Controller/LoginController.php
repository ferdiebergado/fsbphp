<?php declare (strict_types = 1);

namespace App\Controller;

use Psr\Http\Message \{
    ResponseInterface, ServerRequestInterface
};
use App\Model\User;
use FSB\Session\SessionHelper;
use App\Command \{
    LogoutCommand, LoginCommand
};

class LoginController extends Controller
{
    public function show(ServerRequestInterface $request) : ResponseInterface
    {
        return $this->view("sections/login");
    }

    public function login(ServerRequestInterface $request) : ResponseInterface
    {
        $session = new SessionHelper($request);
        $redirectPath = $session->getFlash('REDIRECT_PATH');
        // die(var_dump($redirectPath));
        $body = $request->getParsedBody();
        $session->flash('old', $body);
        $this->validator
            ->rule('required', ['email', 'password'])
            ->rule('email', 'email')
            ->rule('lengthMin', 'password', 8);
        $invalid = "Invalid input.";
        if (!$this->validator->validate()) {
            $errors = $this->validator->errors();
            $statuscode = 403;
            $session->flash('errors', $errors);
            $session->flash('error', $invalid);
            return $this->response->withStatus($statuscode)->withHeader('Location', $request->getUri()->getPath());
        }

        $login = new LoginCommand($session, $body);
        $loggedIn = $this->commandBus->handle($login);

        if ($loggedIn) {
            return $this->response->withHeader('Location', $redirectPath);
        }

        $statuscode = 401;
        return $this->response->withStatus($statuscode)->withHeader('Location', $request->getUri()->getPath());
    }

    public function logout(ServerRequestInterface $request) : ResponseInterface
    {
        $session = new SessionHelper($request);
        $logout = new LogoutCommand($session);
        $this->commandBus->handle($logout);
        return $this->response->withHeader('Location', '/login');
    }
}

<?php declare (strict_types = 1);

namespace App\Controller;

use Psr\Http\Message \{
    ResponseInterface, ServerRequestInterface
};
use App\Model\User;
use App\Command \{
    LogoutCommand, LoginCommand
};

class LoginController extends Controller
{
    protected $redirectPathOnLogout = '/login';

    public function show(ServerRequestInterface $request) : ResponseInterface
    {
        return $this->view("sections/login");
    }

    public function login(ServerRequestInterface $request) : ResponseInterface
    {
        $body = $request->getParsedBody();
        $this->session->flash('old', $body);
        $this->validator
            ->rule('required', ['email', 'password'])
            ->rule('email', 'email')
            ->rule('lengthMin', 'password', 8);
        if (!$this->validator->validate()) {
            $errors = $this->validator->errors();
            $statuscode = 403;
            $invalid = "Invalid input.";
            $this->session->flash('errors', $errors);
            $this->session->flash('error', $invalid);
            return $this->response->withStatus($statuscode)->withHeader('Location', $request->getUri()->getPath());
        }

        $login = new LoginCommand($this->session, $body);
        $loggedIn = $this->commandBus->handle($login);

        if ($loggedIn) {
            $redirectPath = $this->session->get('REDIRECT_PATH');
            $this->session->set('REDIRECT_PATH', null);
            return $this->response->withHeader('Location', $redirectPath);
        }

        $statuscode = 401;
        return $this->response->withStatus($statuscode)->withHeader('Location', $request->getUri()->getPath());
    }

    public function logout(ServerRequestInterface $request) : ResponseInterface
    {
        $logout = new LogoutCommand($this->session);
        $this->commandBus->handle($logout);
        return $this->response->withHeader('Location', $this->redirectPathOnLogout);
    }
}

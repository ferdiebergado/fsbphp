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
    /**
     * Show the Login form.
     * 
     * @uri string "/login"
     * @method string "GET"
     * 
     * @var Psr\Http\Message\ServerRequestInterface $request
     * 
     * @return Psr\Http\Message\ResponseInterface
     */
    public function show(ServerRequestInterface $request) : ResponseInterface
    {
        return $this->view("sections/login");
    }

    /**
     * Handle user login. 
     * 
     * @uri string "/login"
     * @method string "POST"
     * 
     * @var Psr\Http\Message\ServerRequestInterface $request
     * 
     * @return Psr\Http\Message\ResponseInterface 
     */
    public function login(ServerRequestInterface $request) : ResponseInterface
    {
        $session = new SessionHelper($request);
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
            $redirectPath = $session->get('REDIRECT_PATH');
            // if (null === $redirectPath && $redirectPath !== '/login') {
            //     $redirectPath = '/';
            // }
            // $session->set('REDIRECT_PATH', null);
            return $this->response->withHeader('Location', '/');
        }

        $statuscode = 401;
        return $this->response->withStatus($statuscode)->withHeader('Location', $request->getUri()->getPath());
    }

    /**
     * Logout.
     * 
     * @uri string "/logout"
     * @method string "POST"
     * 
     * @var Psr\Http\Message\ServerRequestInterface $request
     * 
     * @return Psr\Http\Message\ResponseInterface 
     */
    public function logout(ServerRequestInterface $request) : ResponseInterface
    {
        $session = new SessionHelper($request);
        $logout = new LogoutCommand($session);
        $this->commandBus->handle($logout);
        return $this->response->withHeader('Location', '/login');
    }
}

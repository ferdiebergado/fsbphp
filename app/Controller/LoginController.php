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
use Zend\Diactoros\Response\RedirectResponse;
use Valitron\Validator;

class LoginController extends Controller
{
    protected $loginPath = "/login";
    protected $redirectPath = "/";

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
        $body = $request->getParsedBody();
        $session = new SessionHelper($request);
        $session->flash('old', $body);
        $validator = new Validator($body);
        $validator
            ->rule('required', ['email', 'password'])
            ->rule('email', 'email')
            ->rule('lengthMin', 'password', 8);
        $invalid = "Invalid input.";
        if (!$validator->validate()) {
            $errors = $validator->errors();
            // $statuscode = 403;
            $session->flash('errors', $errors);
            $session->flash('error', $invalid);
            return new RedirectResponse($this->loginPath);
            // return $this->response->withStatus($statuscode)->withHeader('Location', $request->getUri()->getPath());
        }

        $login = new LoginCommand($session, $body);
        $loggedIn = $this->commandBus->handle($login);

        if ($loggedIn) {
            // $redirectPath = $session->get('REDIRECT_PATH');
            // if (null === $redirectPath && $redirectPath !== '/login') {
            //     $redirectPath = '/';
            // }
            // $session->set('REDIRECT_PATH', null);
            return new RedirectResponse($this->redirectPath);
            // return $this->response->withHeader('Location', '/');
        }

        // $statuscode = 401;
        // return $this->response->withStatus($statuscode)->withHeader('Location', $request->getUri()->getPath());
        return new RedirectResponse($this->loginPath);
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
        return new RedirectResponse($this->loginPath);
    }
}

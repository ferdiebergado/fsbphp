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
        $session = $request->getAttribute('session');
        $segment = $request->getAttribute('segment');
        $validator = new Validator($body);
        $validator
            ->rule('required', ['email', 'password'])
            ->rule('email', 'email')
            ->rule('lengthMin', 'password', 8);
        $invalid = "Invalid input.";
        if (!$validator->validate()) {
            $errors = $validator->errors();
            $segment->setFlash('old', $body);
            $segment->setFlash('errors', $errors);
            $segment->setFlash('error', $invalid);
            return new RedirectResponse($this->loginPath);
        }

        $ip = $request->getAttribute('client-ip');
        $ssl = $request->getAttribute('ssl');
        $userAgent = $request->getAttribute('user-agent');
        $login = new LoginCommand($session, $segment, $ip, $userAgent, $ssl, $body);
        $loggedIn = $this->commandBus->handle($login);

        if ($loggedIn) {
            $redirectPath = $segment->get('REDIRECT_PATH');
            if (null === $redirectPath || $redirectPath === $this->loginPath) {
                $redirectPath = '/';
            }
            return new RedirectResponse($redirectPath);
        }

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
        $session = $request->getAttribute('session');
        $logout = new LogoutCommand($session);
        $this->commandBus->handle($logout);
        return new RedirectResponse($this->loginPath);
    }
}

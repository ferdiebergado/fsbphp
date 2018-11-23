<?php declare (strict_types = 1);

namespace App\Controller;

use Psr\Http\Message \{
    ResponseInterface, ServerRequestInterface
};
use App\Model\User;
use App\Command\LogoutCommand;

class LoginController extends Controller
{
    public function show(ServerRequestInterface $request) : ResponseInterface
    {
        return $this->view("sections/login");
    }

    public function login(ServerRequestInterface $request) : ResponseInterface
    {
        $session = $request->getAttribute('session');
        $body = $request->getParsedBody();
        // $session->set('old', $body);
        $this->validator
            ->rule('required', ['email', 'password'])
            ->rule('email', 'email')
            ->rule('lengthMin', 'password', 8);
        $invalid = "Invalid input.";
        if (!$this->validator->validate()) {
            $errors = $this->validator->errors();
            $statuscode = 403;
            $session->set('errors', $errors);
            // $session->flash('error', $invalid);
            return $this->response->withStatus($statuscode)->withHeader('Location', $request->getUri()->getPath());
        }
        $error = 'Invalid username or password.';
        $user = User::where('email', $body['email'])->first();
        if (isset($user)) {
            $hash = $user->password;
            if (password_verify($body['password'], $hash)) {
                if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                    $newhash = password_hash($password, PASSWORD_DEFAULT);
                    $user->update(['password' => $newhash]);
                }
                // cache_remember('user_' . $user['id'], 30, $authuser);
                $user->update(['last_login' => date('Y-m-d H:i:s')]);
                $session->regenerate();
                $session->set('user', $user);
                // $session->flash('status', 'You are now logged in!');
                return $this->response->withHeader('Location', '/');
            }
        }
        $statuscode = 401;
        $session->set('errors', ['email' => [0 => $error]]);
        // $session->flash('error', $invalid);
        $this->template->addGlobal('session', $session);
        return $this->response->withStatus($statuscode)->withHeader('Location', $request->getUri()->getPath());
    }

    public function logout(ServerRequestInterface $request) : ResponseInterface
    {
        $session = $request->getAttribute('session');
        $logout = new LogoutCommand($session);
        $this->commandBus->handle($logout);
        return $this->response->withHeader('Location', '/');
    }
}

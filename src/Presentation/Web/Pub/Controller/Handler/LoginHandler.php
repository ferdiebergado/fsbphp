<?php declare (strict_types = 1);

namespace Bergado\Presentation\Web\Pub\Controller\Handler;

use Bergado\Domain\Entity\User;
use Bergado\Presentation\Web\Pub\Controller\Command\LoginCommand;

class LoginHandler extends Handler
{
    public function handle(LoginCommand $login) : bool
    {
        $segment = $login->segment;
        $body = $login->body;
        $user = User::where('email', $body['email'])->first();
        if (null !== $user) {
            $hash = $user->password;
            if (password_verify($body['password'], $hash)) {
                if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                    $newhash = password_hash($password, PASSWORD_DEFAULT);
                    $user->update(['password' => $newhash]);
                }
                $ip = $login->ip;
                $userAgent = $login->userAgent;
                $update = [
                    'last_login' => date('Y-m-d H:i:s'),
                    'ipv4' => $ip,
                    'user_agent' => $userAgent
                ];
                $user->update($update);
                $user = $user->toArray();
                $key = 'user_' . $user['id'];
                $this->cache->delete($key);
                $this->cache->recall($key, 30, $user);
                $session = $login->session;
                $session->regenerateId();
                $ssl = $login->ssl;
                $segment->set('IPaddress', $ip);
                $segment->set('userAgent', $userAgent);
                $segment->set('isSsl', $ssl);
                $segment->set('user', $user);
                $segment->setFlash('status', 'You are now logged in!');
                return true;
            }
        }
        $error = "Invalid username or password";
        $segment->setFlash('errors', ['email' => [0 => $error]]);
        $segment->setFlash('error', 'Invalid credentials.');
        return false;
    }
}

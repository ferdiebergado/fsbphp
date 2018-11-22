<?php

namespace App\Handler;

use App\Model\User;
use App\Command\LoginCommand;

class LoginHandler
{
    public function handle(LoginCommand $login)
    {
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
                $session->regenerateId();
                $session->set('user', $user->toArray());
                $session->flash('status', 'You are now logged in!');
                return $this->response->withHeader('Location', '/');
            }
        }
    }
}

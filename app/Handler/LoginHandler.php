<?php

namespace App\Handler;

use App\Model\User;
use App\Command\LoginCommand;

class LoginHandler
{
    public function handle(LoginCommand $login)
    {
        $session = $login->session;
        $body = $login->body;
        $user = User::where('email', $body['email'])->first();
        if (null !== $user) {
            $hash = $user->password;
            if (password_verify($body['password'], $hash)) {
                if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                    $newhash = password_hash($password, PASSWORD_DEFAULT);
                    $user->update(['password' => $newhash]);
                }
                // cache_remember('user_' . $user['id'], 30, $authuser);
                $user->update(['last_login' => date('Y-m-d H:i:s')]);
                $session->regenerateId();
                $session->set('user', $user);
                $session->flash('status', 'You are now logged in!');
                return true;
            }
        }
        $error = "Invalid username or password";
        $session->set('errors', ['email' => [0 => $error]]);
        $session->flash('error', $invalid);
        return false;
    }
}

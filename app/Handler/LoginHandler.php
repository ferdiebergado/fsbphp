<?php

namespace App\Handler;

use App\Model\User;
use App\Command\LoginCommand;

class LoginHandler
{
    public function handle(LoginCommand $login)
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
                $user->update([
                    'last_login' => date('Y-m-d H:i:s'),
                    'ipv4' => $ip,
                    'user_agent' => $userAgent
                ]);
                $user = $user->toArray();
                // cache_remember('user_' . $user['id'], 30, $user);
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

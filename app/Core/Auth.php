<?php

namespace App\Core;

class Auth
{
    public static function login($user)
    {
        $_SESSION['user'] = [
            'id' => $user->id,
            'username' => $user->username
        ];
    }

    public static function isLoggedIn()
    {
        return isset($_SESSION['user']);
    }

    public static function logout()
    {
        session_destroy();
        header('Location: /login');
        exit;
    }
}

<?php

namespace App\Core;

use App\enums\Role;

class Auth
{
    public static function login($user)
    {
        $_SESSION['user'] = $user;
    }

    public static function isLoggedIn()
    {
        return isset($_SESSION['user']) && !empty($_SESSION['user']);
    }

    public static function isRole(Role $role)
    {
        return isset($_SESSION['user']) && $_SESSION['user']['role'] == $role->value;
    }

    public static function logout()
    {
        session_destroy();
        header('Location: /login');
        exit;
    }

    public static function requireAuth(Role $role)
    {
        if (!self::isLoggedIn()) {
            Session::setFlashMessage('red', 'Please login to continue');
            header('location: /login');
            exit;
        }

        if (!self::isRole($role)) {
            Session::setFlashMessage('red', 'not allowed !');
            header('location: /403');
        }
    }
}

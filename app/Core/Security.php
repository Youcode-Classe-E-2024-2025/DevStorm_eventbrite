<?php

namespace App\Core;
session_start();
class Security {

    public static function hash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function verify($password, $hash)
    {
        return password_verify($password, $hash);
    }

    public function Csrftoken(){
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    public static function checkCsrfToken($token){
        return $token === $_SESSION['csrf_token'];
    }




}

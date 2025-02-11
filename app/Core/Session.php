<?php

namespace App\Core;

class Session {
    public static function get($key)
    {
        return $_SESSION[$key];
    }

    public static function set($key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function destroy(): void
    {
        // session_start();
        session_unset();
        session_destroy();
    }
    
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }


    public static function setFlashMessage($type, $message) {
        $_SESSION[$type] = $message;
    }

    public static function hasFlashMessage($type) {
        return isset($_SESSION[$type]);
    }
   
    public static function getFlashMessage($type) {
        if (self::hasFlashMessage($type)) {
            $message = $_SESSION[$type];
            unset($_SESSION[$type]);
            return $message;
        }
        return null;
    }

    public static function setData($name,$content){
        $_SESSION[$name] = $content;
    }
    
    public static function getData($name){
        if (isset($_SESSION[$name])) {
            $content = $_SESSION[$name];
            unset($_SESSION[$name]);
            return $content;
        }
        return null;
    }

    public static function getUser(){
        if (isset($_SESSION['user'])) {
            $content = $_SESSION['user'];
            return $content;
        }
        return null;
    }



}

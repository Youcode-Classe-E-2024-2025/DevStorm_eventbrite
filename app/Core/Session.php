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
        session_start();
        session_unset();
        session_destroy();
    }


}

<?php
/**
 * Simple Session class that allows retrieval of a session variable with
 * a default
 *
 * Copyright 2015-2022 Rob Allen (rob@akrabat.com).
 * License: New-BSD
 */
namespace RKA;

final class Session
{
    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public function get($key, $default = null)
    {
        if (array_key_exists($key, $_SESSION)) {
            return $_SESSION[$key];
        }

        return $default;
    }

    public function set($key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function delete($key): void
    {
        if (array_key_exists($key, $_SESSION)) {
            unset($_SESSION[$key]);
        }
    }

    public function clearAll(): void
    {
        $_SESSION = [];
    }

    public function __set($key, $value): void
    {
        $this->set($key, $value);
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __isset($key): bool
    {
        return array_key_exists($key, $_SESSION);
    }

    public function __unset($key): void
    {
        $this->delete($key);
    }
}

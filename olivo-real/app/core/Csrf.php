<?php
/** Token contra formularios falsificados (CSRF). En cada <form method="post"> poner <?= Csrf::campo() ?> y validar en el controlador. */
class Csrf
{
    public static function token()
    {
        Auth::iniciar();
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    public static function campo()
    {
        return '<input type="hidden" name="csrf" value="' . e(self::token()) . '">';
    }

    public static function validar($token)
    {
        return is_string($token) && hash_equals(self::token(), $token);
    }
}

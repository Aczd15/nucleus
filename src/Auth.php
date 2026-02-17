<?php

namespace App;

class Auth
{
    public static function hashPassword(string $password, string $pepper): string
    {
        $salted = hash_hmac('sha256', $password, $pepper);
        return password_hash($salted, PASSWORD_DEFAULT);
    }

    public static function verifyPassword(string $password, string $hash, string $pepper): bool
    {
        $salted = hash_hmac('sha256', $password, $pepper);
        return password_verify($salted, $hash);
    }
}

<?php

namespace App\Support;

use Illuminate\Support\Facades\Hash;
use Throwable;

final class PasswordVerifier
{
    // Hash bcrypt valido usado cuando el usuario no existe, para evitar diferencias de tiempo.
    private const DUMMY_HASH = '$2y$12$YVfcCVGhQikeSQGVgAMNquuL1lUTEq5Tus9uy6fVsLH1cTgnyotay';

    public static function check(?string $hash, string $password): bool
    {
        try {
            return Hash::check($password, $hash ?: self::DUMMY_HASH);
        } catch (Throwable) {
            return false;
        }
    }
}

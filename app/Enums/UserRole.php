<?php

namespace App\Enums;

enum UserRole: string
{
    case Student = 'student';
    case Teacher = 'teacher';
    case Admin = 'admin';
    case Developer = 'developer';

    public static function values(): array
    {
        return array_map(fn($c) => $c->value, self::cases());
    }
}

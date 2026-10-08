<?php

declare(strict_types=1);

namespace App\Enum;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Aktif',
            self::INACTIVE => 'Nonaktif',
            self::SUSPENDED => 'Ditangguhkan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::ACTIVE => 'badge-success',
            self::INACTIVE => 'badge-neutral',
            self::SUSPENDED => 'badge-error',
        };
    }
}

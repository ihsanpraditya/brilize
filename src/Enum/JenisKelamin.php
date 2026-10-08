<?php

declare(strict_types=1);

namespace App\Enum;

enum JenisKelamin: string
{
    case LAKI_LAKI = 'L';
    case PEREMPUAN = 'P';

    public function label(): string
    {
        return match ($this) {
            self::LAKI_LAKI => 'Laki-laki',
            self::PEREMPUAN => 'Perempuan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::LAKI_LAKI => 'badge-info',
            self::PEREMPUAN => 'badge-secondary',
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Enum;

enum KategoriPegawai: string
{
    case PENDIDIK = 'pendidik'; // Guru
    case TENAGA_KEPENDIDIKAN = 'tendik'; // Staf TU, Laboran, Pustakawan, Satpam, dll

    public function label(): string
    {
        return match ($this) {
            self::PENDIDIK => 'Tenaga Pendidik (Guru)',
            self::TENAGA_KEPENDIDIKAN => 'Tenaga Kependidikan (Tendik / Staf)',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDIDIK => 'badge-primary',
            self::TENAGA_KEPENDIDIKAN => 'badge-secondary',
        };
    }
}

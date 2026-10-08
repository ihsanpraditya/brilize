<?php

declare(strict_types=1);

namespace App\Enum;

enum StatusKepegawaian: string
{
    case PNS = 'PNS';
    case PPPK = 'PPPK';
    case GTT = 'GTT'; // Guru Tidak Tetap
    case GTY = 'GTY'; // Guru Tetap Yayasan
    case HONORER = 'HONORER';

    public function label(): string
    {
        return match ($this) {
            self::PNS => 'Pegawai Negeri Sipil (PNS)',
            self::PPPK => 'Pegawai Pemerintah dengan Perjanjian Kerja (PPPK)',
            self::GTT => 'Guru Tidak Tetap (GTT)',
            self::GTY => 'Guru Tetap Yayasan (GTY)',
            self::HONORER => 'Tenaga Honorer / Kontrak',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PNS => 'badge-success',
            self::PPPK => 'badge-primary',
            self::GTY => 'badge-info',
            self::GTT => 'badge-warning',
            self::HONORER => 'badge-neutral',
        };
    }
}

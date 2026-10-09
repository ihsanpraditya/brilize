<?php

declare(strict_types=1);

namespace App\Enum;

enum StatusSiswa: string
{
    case AKTIF = 'aktif';
    case LULUS = 'lulus';
    case MUTASI = 'mutasi';
    case KELUAR = 'keluar';
    case NONAKTIF = 'nonaktif';

    public function label(): string
    {
        return match ($this) {
            self::AKTIF => 'Aktif',
            self::LULUS => 'Lulus',
            self::MUTASI => 'Mutasi / Pindah',
            self::KELUAR => 'Keluar / Drop Out',
            self::NONAKTIF => 'Nonaktif',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::AKTIF => 'badge-success',
            self::LULUS => 'badge-primary',
            self::MUTASI => 'badge-warning',
            self::KELUAR => 'badge-error',
            self::NONAKTIF => 'badge-neutral',
        };
    }
}

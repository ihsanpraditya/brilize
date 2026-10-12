<?php

declare(strict_types=1);

namespace App\Enum;

enum JenisMutasi: string
{
    case MASUK = 'masuk';
    case PINDAH_KELAS = 'pindah_kelas';
    case NAIK_KELAS = 'naik_kelas';
    case TINGGAL_KELAS = 'tinggal_kelas';
    case KELUAR = 'keluar';
    case LULUS = 'lulus';
    case DROPOUT = 'dropout';

    public function label(): string
    {
        return match ($this) {
            self::MASUK => 'Mutasi Masuk (Pindahan)',
            self::PINDAH_KELAS => 'Pindah Kelas / Rombel',
            self::NAIK_KELAS => 'Kenaikan Kelas',
            self::TINGGAL_KELAS => 'Tinggal Kelas (Mengulang)',
            self::KELUAR => 'Mutasi Keluar (Pindah Sekolah)',
            self::LULUS => 'Kelulusan / Alumni',
            self::DROPOUT => 'Keluar / Drop Out',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::MASUK => 'badge-info',
            self::PINDAH_KELAS => 'badge-primary',
            self::NAIK_KELAS => 'badge-success',
            self::TINGGAL_KELAS => 'badge-warning',
            self::KELUAR => 'badge-error',
            self::LULUS => 'badge-accent',
            self::DROPOUT => 'badge-neutral',
        };
    }
}

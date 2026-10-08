<?php

declare(strict_types=1);

namespace App\Enum;

enum KelompokMapel: string
{
    case WAJIB = 'wajib'; // Kelompok A (Pendidikan Agama, PPKn, Bahasa Indonesia, Matematika, Sejarah, Bahasa Inggris)
    case UMUM = 'umum'; // Kelompok B (Seni Budaya, PJOK, Prakarya/Informatika)
    case PEMINATAN = 'peminatan'; // Kelompok C (MIPA, IPS, Bahasa, atau Konsentrasi Kejuruan SMK)
    case MULOK = 'mulok'; // Muatan Lokal (Bahasa Daerah, PLHJ, dll)
    case BIMBINGAN = 'bimbingan'; // Bimbingan Konseling (BK) / Pengembangan Diri

    public function label(): string
    {
        return match ($this) {
            self::WAJIB => 'Kelompok A (Wajib)',
            self::UMUM => 'Kelompok B (Umum)',
            self::PEMINATAN => 'Kelompok C (Peminatan / Kejuruan)',
            self::MULOK => 'Muatan Lokal (Mulok)',
            self::BIMBINGAN => 'Bimbingan Konseling (BK)',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::WAJIB => 'badge-primary',
            self::UMUM => 'badge-info',
            self::PEMINATAN => 'badge-secondary',
            self::MULOK => 'badge-accent',
            self::BIMBINGAN => 'badge-warning',
        };
    }
}

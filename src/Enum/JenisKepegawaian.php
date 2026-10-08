<?php

declare(strict_types=1);

namespace App\Enum;

enum JenisKepegawaian: string
{
    case GURU_MAPEL = 'guru_mapel';
    case GURU_BK = 'guru_bk';
    case GURU_KELAS = 'guru_kelas';
    case KEPALA_SEKOLAH = 'kepala_sekolah';
    case TATA_USAHA = 'tata_usaha';
    case STAF = 'staf';

    public function label(): string
    {
        return match ($this) {
            self::GURU_MAPEL => 'Guru Mata Pelajaran',
            self::GURU_BK => 'Guru Bimbingan Konseling (BK)',
            self::GURU_KELAS => 'Guru Kelas / Wali',
            self::KEPALA_SEKOLAH => 'Kepala Sekolah',
            self::TATA_USAHA => 'Tenaga Administrasi / TU',
            self::STAF => 'Staf / Tenaga Kependidikan',
        };
    }
}

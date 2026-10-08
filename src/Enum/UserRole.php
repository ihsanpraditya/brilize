<?php

declare(strict_types=1);

namespace App\Enum;

enum UserRole: string
{
    case SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    case KEPALA_SEKOLAH = 'ROLE_KEPSEK';
    case TATA_USAHA = 'ROLE_TU';
    case BENDAHARA = 'ROLE_BENDAHARA';
    case GURU = 'ROLE_GURU';
    case SISWA = 'ROLE_SISWA';
    case WALI_MURID = 'ROLE_WALI';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Administrator',
            self::KEPALA_SEKOLAH => 'Kepala Sekolah',
            self::TATA_USAHA => 'Staf Tata Usaha (TU)',
            self::BENDAHARA => 'Bendahara / Keuangan',
            self::GURU => 'Guru / Tenaga Pendidik',
            self::SISWA => 'Siswa / Peserta Didik',
            self::WALI_MURID => 'Orang Tua / Wali Murid',
        };
    }
}

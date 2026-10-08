<?php

declare(strict_types=1);

namespace App\Enum;

enum JenisPegawai: string
{
    case GURU = 'guru';
    case TATA_USAHA = 'tata_usaha';
    case BENDAHARA = 'bendahara';
    case PUSTAKAWAN = 'pustakawan';
    case LABORAN = 'laboran';
    case OPERATOR_SEKOLAH = 'operator_sekolah';
    case SATPAM = 'satpam';
    case KEBERSIHAN = 'kebersihan';
    case KEPALA_SEKOLAH = 'kepala_sekolah';
    case LAINNYA = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::GURU => 'Guru / Pengajar',
            self::TATA_USAHA => 'Staf Tata Usaha',
            self::BENDAHARA => 'Bendahara / Keuangan',
            self::PUSTAKAWAN => 'Pustakawan',
            self::LABORAN => 'Laboran',
            self::OPERATOR_SEKOLAH => 'Operator Sekolah (OPS)',
            self::SATPAM => 'Petugas Keamanan (Satpam)',
            self::KEBERSIHAN => 'Petugas Kebersihan',
            self::KEPALA_SEKOLAH => 'Kepala Sekolah',
            self::LAINNYA => 'Lainnya',
        };
    }
}

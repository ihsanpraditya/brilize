<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\TahunPelajaran;
use App\Enum\Semester;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TahunPelajaranFixtures extends Fixture
{
    public const TP_2024_2025_GANJIL = 'tp_2024_2025_ganjil';
    public const TP_2024_2025_GENAP = 'tp_2024_2025_genap';
    public const TP_2025_2026_GANJIL = 'tp_2025_2026_ganjil';
    public const TP_2025_2026_GENAP = 'tp_2025_2026_genap';
    public const TP_2026_2027_GANJIL = 'tp_2026_2027_ganjil'; // Aktif
    public const TP_2026_2027_GENAP = 'tp_2026_2027_genap';

    public function load(ObjectManager $manager): void
    {
        $dataTahun = [
            [
                'ref' => self::TP_2024_2025_GANJIL,
                'tahun' => '2024/2025',
                'semester' => Semester::GANJIL,
                'mulai' => '2024-07-15',
                'selesai' => '2024-12-20',
                'isActive' => false,
            ],
            [
                'ref' => self::TP_2024_2025_GENAP,
                'tahun' => '2024/2025',
                'semester' => Semester::GENAP,
                'mulai' => '2025-01-06',
                'selesai' => '2025-06-20',
                'isActive' => false,
            ],
            [
                'ref' => self::TP_2025_2026_GANJIL,
                'tahun' => '2025/2026',
                'semester' => Semester::GANJIL,
                'mulai' => '2025-07-14',
                'selesai' => '2025-12-19',
                'isActive' => false,
            ],
            [
                'ref' => self::TP_2025_2026_GENAP,
                'tahun' => '2025/2026',
                'semester' => Semester::GENAP,
                'mulai' => '2026-01-05',
                'selesai' => '2026-06-19',
                'isActive' => false,
            ],
            [
                'ref' => self::TP_2026_2027_GANJIL,
                'tahun' => '2026/2027',
                'semester' => Semester::GANJIL,
                'mulai' => '2026-07-13',
                'selesai' => '2026-12-18',
                'isActive' => true,
            ],
            [
                'ref' => self::TP_2026_2027_GENAP,
                'tahun' => '2026/2027',
                'semester' => Semester::GENAP,
                'mulai' => '2027-01-04',
                'selesai' => '2027-06-18',
                'isActive' => false,
            ],
        ];

        foreach ($dataTahun as $item) {
            $tp = new TahunPelajaran();
            $tp->setTahun($item['tahun']);
            $tp->setSemester($item['semester']);
            $tp->setTanggalMulai(new \DateTimeImmutable($item['mulai']));
            $tp->setTanggalSelesai(new \DateTimeImmutable($item['selesai']));
            $tp->setIsActive($item['isActive']);

            $manager->persist($tp);
            $this->addReference($item['ref'], $tp);
        }

        $manager->flush();
    }
}

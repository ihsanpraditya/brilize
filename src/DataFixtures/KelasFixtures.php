<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Kelas;
use App\Entity\TahunPelajaran;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class KelasFixtures extends Fixture implements DependentFixtureInterface
{
    public const KELAS_X_RPL_1 = 'kelas_x_rpl_1';
    public const KELAS_X_RPL_2 = 'kelas_x_rpl_2';
    public const KELAS_X_TKJ_1 = 'kelas_x_tkj_1';
    public const KELAS_X_AKL_1 = 'kelas_x_akl_1';
    public const KELAS_XI_RPL_1 = 'kelas_xi_rpl_1';
    public const KELAS_XI_RPL_2 = 'kelas_xi_rpl_2';
    public const KELAS_XI_TKJ_1 = 'kelas_xi_tkj_1';
    public const KELAS_XII_RPL_1 = 'kelas_xii_rpl_1';
    public const KELAS_XII_TKJ_1 = 'kelas_xii_tkj_1';

    public function load(ObjectManager $manager): void
    {
        $activeTp = $this->getReference(TahunPelajaranFixtures::TP_2026_2027_GANJIL, TahunPelajaran::class);

        $guruBudi = $this->hasReference('user_guru_budi', User::class)
            ? $this->getReference('user_guru_budi', User::class)
            : null;

        $guruDewi = $this->hasReference('user_guru_dewi', User::class)
            ? $this->getReference('user_guru_dewi', User::class)
            : null;

        $kelasList = [
            // Tingkat 10
            [
                'ref' => self::KELAS_X_RPL_1,
                'nama' => 'X-RPL-1',
                'tingkat' => 10,
                'jurusan' => 'Rekayasa Perangkat Lunak',
                'wali' => $guruBudi,
                'kapasitas' => 36,
                'keterangan' => 'Gedung A, Ruang 101',
            ],
            [
                'ref' => self::KELAS_X_RPL_2,
                'nama' => 'X-RPL-2',
                'tingkat' => 10,
                'jurusan' => 'Rekayasa Perangkat Lunak',
                'wali' => $guruDewi,
                'kapasitas' => 36,
                'keterangan' => 'Gedung A, Ruang 102',
            ],
            [
                'ref' => self::KELAS_X_TKJ_1,
                'nama' => 'X-TKJ-1',
                'tingkat' => 10,
                'jurusan' => 'Teknik Komputer dan Jaringan',
                'wali' => null,
                'kapasitas' => 36,
                'keterangan' => 'Gedung A, Ruang 103',
            ],
            [
                'ref' => self::KELAS_X_AKL_1,
                'nama' => 'X-AKL-1',
                'tingkat' => 10,
                'jurusan' => 'Akuntansi dan Keuangan Lembaga',
                'wali' => null,
                'kapasitas' => 36,
                'keterangan' => 'Gedung B, Ruang 201',
            ],

            // Tingkat 11
            [
                'ref' => self::KELAS_XI_RPL_1,
                'nama' => 'XI-RPL-1',
                'tingkat' => 11,
                'jurusan' => 'Rekayasa Perangkat Lunak',
                'wali' => null,
                'kapasitas' => 36,
                'keterangan' => 'Gedung B, Ruang 202',
            ],
            [
                'ref' => self::KELAS_XI_RPL_2,
                'nama' => 'XI-RPL-2',
                'tingkat' => 11,
                'jurusan' => 'Rekayasa Perangkat Lunak',
                'wali' => null,
                'kapasitas' => 36,
                'keterangan' => 'Gedung B, Ruang 203',
            ],
            [
                'ref' => self::KELAS_XI_TKJ_1,
                'nama' => 'XI-TKJ-1',
                'tingkat' => 11,
                'jurusan' => 'Teknik Komputer dan Jaringan',
                'wali' => null,
                'kapasitas' => 36,
                'keterangan' => 'Gedung B, Ruang 204',
            ],

            // Tingkat 12
            [
                'ref' => self::KELAS_XII_RPL_1,
                'nama' => 'XII-RPL-1',
                'tingkat' => 12,
                'jurusan' => 'Rekayasa Perangkat Lunak',
                'wali' => null,
                'kapasitas' => 36,
                'keterangan' => 'Gedung C, Ruang 301',
            ],
            [
                'ref' => self::KELAS_XII_TKJ_1,
                'nama' => 'XII-TKJ-1',
                'tingkat' => 12,
                'jurusan' => 'Teknik Komputer dan Jaringan',
                'wali' => null,
                'kapasitas' => 36,
                'keterangan' => 'Gedung C, Ruang 302',
            ],
        ];

        foreach ($kelasList as $item) {
            $kelas = new Kelas();
            $kelas->setNamaKelas($item['nama']);
            $kelas->setTingkat($item['tingkat']);
            $kelas->setJurusan($item['jurusan']);
            $kelas->setTahunPelajaran($activeTp);
            $kelas->setWaliKelas($item['wali']);
            $kelas->setKapasitas($item['kapasitas']);
            $kelas->setKeterangan($item['keterangan']);
            $kelas->setIsActive(true);

            $manager->persist($kelas);
            $this->addReference($item['ref'], $kelas);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TahunPelajaranFixtures::class,
            UserFixtures::class,
        ];
    }
}

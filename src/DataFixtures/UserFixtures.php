<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use App\Enum\UserRole;
use App\Enum\UserStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public const DEFAULT_PASSWORD = 'password123';

    public function load(ObjectManager $manager): void
    {
        $hashedPassword = password_hash(self::DEFAULT_PASSWORD, PASSWORD_BCRYPT);

        $usersData = [
            // 1. Super Administrator
            [
                'name' => 'Administrator Utama',
                'email' => 'admin@sekolah.sch.id',
                'username' => 'admin',
                'identifierNumber' => null,
                'roles' => [UserRole::SUPER_ADMIN->value],
                'status' => UserStatus::ACTIVE,
                'phone' => '081234567890',
            ],
            // 2. Kepala Sekolah
            [
                'name' => 'Drs. H. Mulyadi, M.Pd.',
                'email' => 'kepsek@sekolah.sch.id',
                'username' => 'kepsek',
                'identifierNumber' => '196803151992031004', // NIP
                'roles' => [UserRole::KEPALA_SEKOLAH->value],
                'status' => UserStatus::ACTIVE,
                'phone' => '081234567891',
            ],
            // 3. Staf Tata Usaha (TU)
            [
                'name' => 'Siti Rahmah, S.Kom.',
                'email' => 'tu@sekolah.sch.id',
                'username' => 'tatausaha',
                'identifierNumber' => '198507202010012015', // NIP
                'roles' => [UserRole::TATA_USAHA->value],
                'status' => UserStatus::ACTIVE,
                'phone' => '081234567892',
            ],
            // 4. Bendahara / Keuangan (SPP)
            [
                'name' => 'Ahmad Zaki, S.E.',
                'email' => 'bendahara@sekolah.sch.id',
                'username' => 'bendahara',
                'identifierNumber' => '198711052012011003', // NIP
                'roles' => [UserRole::BENDAHARA->value],
                'status' => UserStatus::ACTIVE,
                'phone' => '081234567893',
            ],
            // 5. Guru / Tenaga Pendidik
            [
                'name' => 'Budi Santoso, S.Pd.',
                'email' => 'budi.santoso@sekolah.sch.id',
                'username' => 'guru_budi',
                'identifierNumber' => '198501152010011002', // NIP
                'roles' => [UserRole::GURU->value],
                'status' => UserStatus::ACTIVE,
                'phone' => '081234567894',
            ],
            [
                'name' => 'Dewi Lestari, M.Pd.',
                'email' => 'dewi.lestari@sekolah.sch.id',
                'username' => 'guru_dewi',
                'identifierNumber' => '199004222015022001', // NIP
                'roles' => [UserRole::GURU->value],
                'status' => UserStatus::ACTIVE,
                'phone' => '081234567895',
            ],
            // 6. Siswa / Peserta Didik
            [
                'name' => 'Muhammad Rizky Pratama',
                'email' => 'rizky.pratama@siswa.sekolah.sch.id',
                'username' => 'rizky01',
                'identifierNumber' => '0087654321', // NISN
                'roles' => [UserRole::SISWA->value],
                'status' => UserStatus::ACTIVE,
                'phone' => '085612345678',
            ],
            [
                'name' => 'Anisa Putri Kirana',
                'email' => 'anisa.putri@siswa.sekolah.sch.id',
                'username' => 'anisa02',
                'identifierNumber' => '0087654322', // NISN
                'roles' => [UserRole::SISWA->value],
                'status' => UserStatus::ACTIVE,
                'phone' => '085612345679',
            ],
            // 7. Orang Tua / Wali Murid
            [
                'name' => 'Bambang Pratama, S.T.',
                'email' => 'bambang.pratama@gmail.com',
                'username' => 'wali_rizky',
                'identifierNumber' => null,
                'roles' => [UserRole::WALI_MURID->value],
                'status' => UserStatus::ACTIVE,
                'phone' => '081398765432',
            ],
        ];

        foreach ($usersData as $data) {
            $user = new User();
            $user->setName($data['name']);
            $user->setEmail($data['email']);
            $user->setUsername($data['username']);
            $user->setIdentifierNumber($data['identifierNumber']);
            $user->setPassword($hashedPassword);
            $user->setRoles($data['roles']);
            $user->setStatus($data['status']);
            $user->setPhone($data['phone']);

            $manager->persist($user);
        }

        $manager->flush();
    }
}

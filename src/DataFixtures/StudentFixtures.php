<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Kelas;
use App\Entity\Siswa;
use App\Entity\User;
use App\Enum\JenisKelamin;
use App\Enum\StatusSiswa;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class StudentFixtures extends Fixture implements DependentFixtureInterface
{
    public const SISWA_RIZKY = 'siswa_rizky';
    public const SISWA_ANISA = 'siswa_anisa';
    public const SISWA_FAJAR = 'siswa_fajar';
    public const SISWA_NURUL = 'siswa_nurul';
    public const SISWA_DIMAS = 'siswa_dimas';
    public const SISWA_AULIA = 'siswa_aulia';
    public const SISWA_HENDRA = 'siswa_hendra';
    public const SISWA_SITI = 'siswa_siti';
    public const SISWA_BAYU = 'siswa_bayu';
    public const SISWA_TIARA = 'siswa_tiara';

    public function load(ObjectManager $manager): void
    {
        $kelasXRpl1 = $this->getReference(KelasFixtures::KELAS_X_RPL_1, Kelas::class);
        $kelasXRpl2 = $this->getReference(KelasFixtures::KELAS_X_RPL_2, Kelas::class);
        $kelasXTkj1 = $this->getReference(KelasFixtures::KELAS_X_TKJ_1, Kelas::class);
        $kelasXiRpl1 = $this->getReference(KelasFixtures::KELAS_XI_RPL_1, Kelas::class);
        $kelasXiiRpl1 = $this->getReference(KelasFixtures::KELAS_XII_RPL_1, Kelas::class);

        $userRizky = $this->hasReference('user_rizky01', User::class)
            ? $this->getReference('user_rizky01', User::class)
            : null;

        $userAnisa = $this->hasReference('user_anisa02', User::class)
            ? $this->getReference('user_anisa02', User::class)
            : null;

        $studentsData = [
            // 1. Siswa X-RPL-1 (Siap diuji Kenaikan Kelas / Pindah Rombel)
            [
                'ref' => self::SISWA_RIZKY,
                'nis' => '20261001',
                'nisn' => '0087654321',
                'nik' => '3201011505080001',
                'namaLengkap' => 'Muhammad Rizky Pratama',
                'namaPanggilan' => 'Rizky',
                'namaArab' => 'محمّد رزقي برتاما',
                'jenisKelamin' => JenisKelamin::LAKI_LAKI,
                'tempatLahir' => 'Jakarta',
                'tanggalLahir' => '2008-05-15',
                'agama' => 'Islam',
                'alamat' => 'Jl. Merdeka No. 45, RT 02 / RW 03',
                'kelurahan' => 'Cilandak Barat',
                'kecamatan' => 'Cilandak',
                'kabupatenKota' => 'Jakarta Selatan',
                'provinsi' => 'DKI Jakarta',
                'kodePos' => '12430',
                'nomorHp' => '085612345678',
                'email' => 'rizky.pratama@siswa.sekolah.sch.id',
                'kelas' => $kelasXRpl1,
                'status' => StatusSiswa::AKTIF,
                'namaAyah' => 'Bambang Pratama, S.T.',
                'pekerjaanAyah' => 'Karyawan Swasta',
                'noHpAyah' => '081398765432',
                'namaIbu' => 'Ratna Sari',
                'pekerjaanIbu' => 'Ibu Rumah Tangga',
                'noHpIbu' => '081398765433',
                'asalSekolah' => 'SMP Negeri 1 Jakarta',
                'user' => $userRizky,
            ],
            [
                'ref' => self::SISWA_ANISA,
                'nis' => '20261002',
                'nisn' => '0087654322',
                'nik' => '3201015208080002',
                'namaLengkap' => 'Anisa Putri Kirana',
                'namaPanggilan' => 'Anisa',
                'namaArab' => 'أنيسة فوتري كيرانا',
                'jenisKelamin' => JenisKelamin::PEREMPUAN,
                'tempatLahir' => 'Bandung',
                'tanggalLahir' => '2008-08-12',
                'agama' => 'Islam',
                'alamat' => 'Jl. Anggrek No. 12',
                'kelurahan' => 'Kebayoran Lama',
                'kecamatan' => 'Kebayoran Baru',
                'kabupatenKota' => 'Jakarta Selatan',
                'provinsi' => 'DKI Jakarta',
                'kodePos' => '12240',
                'nomorHp' => '085612345679',
                'email' => 'anisa.putri@siswa.sekolah.sch.id',
                'kelas' => $kelasXRpl1,
                'status' => StatusSiswa::AKTIF,
                'namaAyah' => 'Gunawan Wibisono',
                'pekerjaanAyah' => 'PNS',
                'noHpAyah' => '081234567811',
                'namaIbu' => 'Dewi Suryani',
                'pekerjaanIbu' => 'Guru',
                'noHpIbu' => '081234567812',
                'asalSekolah' => 'SMP Negeri 2 Bandung',
                'user' => $userAnisa,
            ],
            [
                'ref' => self::SISWA_FAJAR,
                'nis' => '20261003',
                'nisn' => '0087654323',
                'nik' => '3201011810080003',
                'namaLengkap' => 'Fajar Hidayatullah',
                'namaPanggilan' => 'Fajar',
                'namaArab' => 'فجر هداية الله',
                'jenisKelamin' => JenisKelamin::LAKI_LAKI,
                'tempatLahir' => 'Bogor',
                'tanggalLahir' => '2008-10-18',
                'agama' => 'Islam',
                'alamat' => 'Perumahan Griya Indah Blok C2/15',
                'kelurahan' => 'Tajur',
                'kecamatan' => 'Bogor Timur',
                'kabupatenKota' => 'Kota Bogor',
                'provinsi' => 'Jawa Barat',
                'kodePos' => '16141',
                'nomorHp' => '081299887766',
                'email' => 'fajar.h@siswa.sekolah.sch.id',
                'kelas' => $kelasXRpl1,
                'status' => StatusSiswa::AKTIF,
                'namaAyah' => 'Hidayat Nur',
                'pekerjaanAyah' => 'Wiraswasta',
                'noHpAyah' => '081288776655',
                'namaIbu' => 'Nuraini',
                'pekerjaanIbu' => 'Ibu Rumah Tangga',
                'noHpIbu' => '081288776656',
                'asalSekolah' => 'MTs Negeri 1 Bogor',
                'user' => null,
            ],

            // 2. Siswa X-RPL-2 (Siap diuji Pindah Kelas ke X-RPL-1 atau Mutasi Keluar)
            [
                'ref' => self::SISWA_NURUL,
                'nis' => '20261004',
                'nisn' => '0087654324',
                'nik' => '3201014503080004',
                'namaLengkap' => 'Nurul Izzati Azzahra',
                'namaPanggilan' => 'Nurul',
                'namaArab' => 'نور العزّة الزهراء',
                'jenisKelamin' => JenisKelamin::PEREMPUAN,
                'tempatLahir' => 'Surabaya',
                'tanggalLahir' => '2008-03-05',
                'agama' => 'Islam',
                'alamat' => 'Jl. Mawar No. 8',
                'kelurahan' => 'Cilandak Timur',
                'kecamatan' => 'Pasar Minggu',
                'kabupatenKota' => 'Jakarta Selatan',
                'provinsi' => 'DKI Jakarta',
                'kodePos' => '12560',
                'nomorHp' => '081377889900',
                'email' => 'nurul.izzati@siswa.sekolah.sch.id',
                'kelas' => $kelasXRpl2,
                'status' => StatusSiswa::AKTIF,
                'namaAyah' => 'Ahmad Syukri',
                'pekerjaanAyah' => 'Dosen',
                'noHpAyah' => '081366554433',
                'namaIbu' => 'Fatimah Zahra',
                'pekerjaanIbu' => 'PNS',
                'noHpIbu' => '081366554434',
                'asalSekolah' => 'SMP Islam Al-Azhar',
                'user' => null,
            ],
            [
                'ref' => self::SISWA_DIMAS,
                'nis' => '20261005',
                'nisn' => '0087654325',
                'nik' => '3201012211080005',
                'namaLengkap' => 'Dimas Arya Pamungkas',
                'namaPanggilan' => 'Dimas',
                'namaArab' => null,
                'jenisKelamin' => JenisKelamin::LAKI_LAKI,
                'tempatLahir' => 'Semarang',
                'tanggalLahir' => '2008-11-22',
                'agama' => 'Islam',
                'alamat' => 'Jl. Melati Raya No. 19',
                'kelurahan' => 'Pancoran',
                'kecamatan' => 'Pancoran',
                'kabupatenKota' => 'Jakarta Selatan',
                'provinsi' => 'DKI Jakarta',
                'kodePos' => '12780',
                'nomorHp' => '081266554411',
                'email' => 'dimas.arya@siswa.sekolah.sch.id',
                'kelas' => $kelasXRpl2,
                'status' => StatusSiswa::AKTIF,
                'namaAyah' => 'Sugeng Pamungkas',
                'pekerjaanAyah' => 'Wiraswasta',
                'noHpAyah' => '081255443322',
                'namaIbu' => 'Sri Wahyuni',
                'pekerjaanIbu' => 'Pedagang',
                'noHpIbu' => '081255443323',
                'asalSekolah' => 'SMP Negeri 5 Semarang',
                'user' => null,
            ],

            // 3. Siswa X-TKJ-1 (Siswa Pindahan / Mutasi Masuk dari sekolah lain)
            [
                'ref' => self::SISWA_AULIA,
                'nis' => '20261006',
                'nisn' => '0087654326',
                'nik' => '3201016004080006',
                'namaLengkap' => 'Aulia Rahmadani',
                'namaPanggilan' => 'Aulia',
                'namaArab' => 'أولياء رحمة داني',
                'jenisKelamin' => JenisKelamin::PEREMPUAN,
                'tempatLahir' => 'Medan',
                'tanggalLahir' => '2008-04-20',
                'agama' => 'Islam',
                'alamat' => 'Komplek Permata Hijau Blok D No. 3',
                'kelurahan' => 'Grogol',
                'kecamatan' => 'Kebayoran Lama',
                'kabupatenKota' => 'Jakarta Selatan',
                'provinsi' => 'DKI Jakarta',
                'kodePos' => '12210',
                'nomorHp' => '081388771122',
                'email' => 'aulia.r@siswa.sekolah.sch.id',
                'kelas' => $kelasXTkj1,
                'status' => StatusSiswa::AKTIF,
                'namaAyah' => 'Rahmat Hidayat',
                'pekerjaanAyah' => 'TNI-AD',
                'noHpAyah' => '081377665544',
                'namaIbu' => 'Dahlia Siregar',
                'pekerjaanIbu' => 'Bidan',
                'noHpIbu' => '081377665545',
                'asalSekolah' => 'SMK Negeri 1 Medan (Pindahan)',
                'user' => null,
            ],

            // 4. Siswa Tingkat 11 (XI-RPL-1) - Siap diuji Kenaikan ke Kelas 12 atau Tinggal Kelas
            [
                'ref' => self::SISWA_HENDRA,
                'nis' => '20251001',
                'nisn' => '0077654311',
                'nik' => '3201011402070007',
                'namaLengkap' => 'Hendra Kurniawan',
                'namaPanggilan' => 'Hendra',
                'namaArab' => null,
                'jenisKelamin' => JenisKelamin::LAKI_LAKI,
                'tempatLahir' => 'Yogyakarta',
                'tanggalLahir' => '2007-02-14',
                'agama' => 'Islam',
                'alamat' => 'Jl. Kenanga No. 77',
                'kelurahan' => 'Tebet Timur',
                'kecamatan' => 'Tebet',
                'kabupatenKota' => 'Jakarta Selatan',
                'provinsi' => 'DKI Jakarta',
                'kodePos' => '12820',
                'nomorHp' => '085711223344',
                'email' => 'hendra.k@siswa.sekolah.sch.id',
                'kelas' => $kelasXiRpl1,
                'status' => StatusSiswa::AKTIF,
                'namaAyah' => 'Kurnia Santoso',
                'pekerjaanAyah' => 'Arsitek',
                'noHpAyah' => '081244332211',
                'namaIbu' => 'Endang Sulastri',
                'pekerjaanIbu' => 'Ibu Rumah Tangga',
                'noHpIbu' => '081244332212',
                'asalSekolah' => 'SMP Negeri 3 Yogyakarta',
                'user' => null,
            ],
            [
                'ref' => self::SISWA_SITI,
                'nis' => '20251002',
                'nisn' => '0077654312',
                'nik' => '3201015907070008',
                'namaLengkap' => 'Siti Nurhaliza',
                'namaPanggilan' => 'Siti',
                'namaArab' => 'ستي نور حليمة',
                'jenisKelamin' => JenisKelamin::PEREMPUAN,
                'tempatLahir' => 'Solo',
                'tanggalLahir' => '2007-07-19',
                'agama' => 'Islam',
                'alamat' => 'Jl. Flamboyan No. 5',
                'kelurahan' => 'Pejaten Barat',
                'kecamatan' => 'Pasar Minggu',
                'kabupatenKota' => 'Jakarta Selatan',
                'provinsi' => 'DKI Jakarta',
                'kodePos' => '12510',
                'nomorHp' => '085722334455',
                'email' => 'siti.nurhaliza@siswa.sekolah.sch.id',
                'kelas' => $kelasXiRpl1,
                'status' => StatusSiswa::AKTIF,
                'namaAyah' => 'Halim Kusuma',
                'pekerjaanAyah' => 'PNS',
                'noHpAyah' => '081311223344',
                'namaIbu' => 'Siti Aminah',
                'pekerjaanIbu' => 'Ibu Rumah Tangga',
                'noHpIbu' => '081311223345',
                'asalSekolah' => 'SMP Negeri 1 Solo',
                'user' => null,
            ],

            // 5. Siswa Tingkat 12 (XII-RPL-1) - Siap diuji Kelulusan (LULUS)
            [
                'ref' => self::SISWA_BAYU,
                'nis' => '20241001',
                'nisn' => '0067654201',
                'nik' => '3201010901060009',
                'namaLengkap' => 'Bayu Setiawan',
                'namaPanggilan' => 'Bayu',
                'namaArab' => null,
                'jenisKelamin' => JenisKelamin::LAKI_LAKI,
                'tempatLahir' => 'Malang',
                'tanggalLahir' => '2006-01-09',
                'agama' => 'Islam',
                'alamat' => 'Jl. Cempaka Putih Tengah No. 22',
                'kelurahan' => 'Cempaka Putih',
                'kecamatan' => 'Cempaka Putih',
                'kabupatenKota' => 'Jakarta Pusat',
                'provinsi' => 'DKI Jakarta',
                'kodePos' => '10510',
                'nomorHp' => '087811223344',
                'email' => 'bayu.setiawan@siswa.sekolah.sch.id',
                'kelas' => $kelasXiiRpl1,
                'status' => StatusSiswa::AKTIF,
                'namaAyah' => 'Tri Setiawan',
                'pekerjaanAyah' => 'Manajer',
                'noHpAyah' => '081122334455',
                'namaIbu' => 'Sri Rejeki',
                'pekerjaanIbu' => 'Notaris',
                'noHpIbu' => '081122334456',
                'asalSekolah' => 'SMP Negeri 1 Malang',
                'user' => null,
            ],

            // 6. Siswa yang sudah berstatus Mutasi Keluar (untuk testing data mutasi keluar yang ada)
            [
                'ref' => self::SISWA_TIARA,
                'nis' => '20261099',
                'nisn' => '0087654399',
                'nik' => '3201017012080010',
                'namaLengkap' => 'Tiara Andini Putri',
                'namaPanggilan' => 'Tiara',
                'namaArab' => 'تيارا أنديني فوتري',
                'jenisKelamin' => JenisKelamin::PEREMPUAN,
                'tempatLahir' => 'Surabaya',
                'tanggalLahir' => '2008-12-30',
                'agama' => 'Islam',
                'alamat' => 'Jl. Dharmawangsa No. 88',
                'kelurahan' => 'Kebayoran Baru',
                'kecamatan' => 'Kebayoran Baru',
                'kabupatenKota' => 'Jakarta Selatan',
                'provinsi' => 'DKI Jakarta',
                'kodePos' => '12160',
                'nomorHp' => '081233445566',
                'email' => 'tiara.andini@siswa.sekolah.sch.id',
                'kelas' => null,
                'status' => StatusSiswa::MUTASI,
                'namaAyah' => 'Andi Wijaya',
                'pekerjaanAyah' => 'Pengusaha',
                'noHpAyah' => '081277889911',
                'namaIbu' => 'Maya Kartika',
                'pekerjaanIbu' => 'Dokter',
                'noHpIbu' => '081277889912',
                'asalSekolah' => 'SMP Negeri 6 Surabaya',
                'user' => null,
            ],
        ];

        foreach ($studentsData as $item) {
            $siswa = new Siswa();
            $siswa->setNis($item['nis']);
            $siswa->setNisn($item['nisn']);
            $siswa->setNik($item['nik']);
            $siswa->setNamaLengkap($item['namaLengkap']);
            $siswa->setNamaPanggilan($item['namaPanggilan']);
            $siswa->setNamaArab($item['namaArab']);
            $siswa->setJenisKelamin($item['jenisKelamin']);
            $siswa->setTempatLahir($item['tempatLahir']);
            $siswa->setTanggalLahir(new \DateTimeImmutable($item['tanggalLahir']));
            $siswa->setAgama($item['agama']);
            $siswa->setAlamat($item['alamat']);
            $siswa->setKelurahan($item['kelurahan']);
            $siswa->setKecamatan($item['kecamatan']);
            $siswa->setKabupatenKota($item['kabupatenKota']);
            $siswa->setProvinsi($item['provinsi']);
            $siswa->setKodePos($item['kodePos']);
            $siswa->setNomorHp($item['nomorHp']);
            $siswa->setEmail($item['email']);
            $siswa->setKelas($item['kelas']);
            $siswa->setStatus($item['status']);
            $siswa->setNamaAyah($item['namaAyah']);
            $siswa->setPekerjaanAyah($item['pekerjaanAyah']);
            $siswa->setNoHpAyah($item['noHpAyah']);
            $siswa->setNamaIbu($item['namaIbu']);
            $siswa->setPekerjaanIbu($item['pekerjaanIbu']);
            $siswa->setNoHpIbu($item['noHpIbu']);
            $siswa->setAsalSekolah($item['asalSekolah']);
            $siswa->setUser($item['user']);
            $siswa->setIsActive($item['status'] === StatusSiswa::AKTIF);

            $manager->persist($siswa);
            $this->addReference($item['ref'], $siswa);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            KelasFixtures::class,
            UserFixtures::class,
        ];
    }
}

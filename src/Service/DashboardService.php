<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\UserRole;
use App\Enum\UserStatus;
use App\Repository\UserRepository;

final readonly class DashboardService
{
    public function __construct(
        private UserRepository $userRepository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getDashboardSummary(?string $userIdentifier = null): array
    {
        $totalUsers = count($this->userRepository->findAll());
        $activeUsers = count($this->userRepository->findBy(['status' => UserStatus::ACTIVE]));

        return [
            'academicYear' => [
                'name' => '2026/2027',
                'semester' => 'Ganjil',
                'isActive' => true,
            ],
            'stats' => [
                'totalSiswa' => [
                    'label' => 'Total Siswa Aktif',
                    'value' => '1,248',
                    'sub' => '+32 siswa baru semester ini',
                    'badge' => '+2.6%',
                    'trend' => 'up',
                ],
                'totalGuru' => [
                    'label' => 'Guru & Tenaga Pendidik',
                    'value' => '74',
                    'sub' => '12 wali kelas & 62 guru mapel',
                    'badge' => 'Lengkap',
                    'trend' => 'neutral',
                ],
                'presensiHariIni' => [
                    'label' => 'Kehadiran Hari Ini',
                    'value' => '98.2%',
                    'sub' => '1,226 hadir | 14 izin | 8 sakit',
                    'badge' => 'Sangat Baik',
                    'trend' => 'up',
                ],
                'pembayaranSPP' => [
                    'label' => 'Realisasi SPP Bulan Ini',
                    'value' => 'Rp 187.200.000',
                    'sub' => '84% target bulan berjalan',
                    'badge' => '84%',
                    'trend' => 'up',
                ],
            ],
            'recentActivities' => [
                [
                    'id' => 1,
                    'title' => 'Input Nilai Formatif 2 - Matematika',
                    'actor' => 'Budi Santoso, S.Pd.',
                    'time' => '15 menit yang lalu',
                    'type' => 'akademik',
                    'badge' => 'Nilai',
                    'badgeColor' => 'badge-info',
                ],
                [
                    'id' => 2,
                    'title' => 'Pembayaran SPP Oktober 2026 (Lunas)',
                    'actor' => 'Muhammad Rizky Pratama (X-RPL-1)',
                    'time' => '42 menit yang lalu',
                    'type' => 'keuangan',
                    'badge' => 'SPP',
                    'badgeColor' => 'badge-success',
                ],
                [
                    'id' => 3,
                    'title' => 'Presensi Harian Kelas XI-IPA-2',
                    'actor' => 'Dewi Lestari, M.Pd.',
                    'time' => '1 jam yang lalu',
                    'type' => 'presensi',
                    'badge' => 'Absensi',
                    'badgeColor' => 'badge-primary',
                ],
                [
                    'id' => 4,
                    'title' => 'Pendaftaran Siswa Pindahan',
                    'actor' => 'Siti Rahmah, S.Kom. (TU)',
                    'time' => '3 jam yang lalu',
                    'type' => 'mutasi',
                    'badge' => 'Tata Usaha',
                    'badgeColor' => 'badge-warning',
                ],
            ],
            'todaySchedule' => [
                [
                    'id' => 1,
                    'jam' => '07:30 - 09:00',
                    'mapel' => 'Matematika Terapan',
                    'kelas' => 'X-RPL-1',
                    'guru' => 'Budi Santoso, S.Pd.',
                    'ruangan' => 'Lab Komputer 2',
                ],
                [
                    'id' => 2,
                    'jam' => '09:15 - 10:45',
                    'mapel' => 'Bahasa Inggris Lanjut',
                    'kelas' => 'XI-TKJ-2',
                    'guru' => 'Dewi Lestari, M.Pd.',
                    'ruangan' => 'Ruang 204',
                ],
                [
                    'id' => 3,
                    'jam' => '11:00 - 12:30',
                    'mapel' => 'Pemrograman Web & Backend',
                    'kelas' => 'XII-RPL-1',
                    'guru' => 'Budi Santoso, S.Pd.',
                    'ruangan' => 'Lab Komputer 1',
                ],
            ],
            'announcements' => [
                [
                    'id' => 1,
                    'title' => 'Pelaksanaan Penilaian Tengah Semester (PTS) Ganjil',
                    'date' => '12 - 17 Oktober 2026',
                    'author' => 'Wakasek Kurikulum',
                    'content' => 'Seluruh guru mapel diharapkan menyelesaikan pengunggahan bank soal ke sistem paling lambat hari Jumat.',
                    'isImportant' => true,
                ],
                [
                    'id' => 2,
                    'title' => 'Sosialisasi Kartu Pelajar Digital & Pembayaran Virtual',
                    'date' => '10 Oktober 2026',
                    'author' => 'Bagian Keuangan & TU',
                    'content' => 'Buku panduan pembayaran SPP online telah dibagikan melalui portal wali murid.',
                    'isImportant' => false,
                ],
            ],
        ];
    }
}

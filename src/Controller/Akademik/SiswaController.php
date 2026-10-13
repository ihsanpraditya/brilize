<?php

declare(strict_types=1);

namespace App\Controller\Akademik;

use App\DTO\Siswa\CreateSiswaDTO;
use App\DTO\Siswa\ImportSiswaBatchDTO;
use App\DTO\Siswa\UpdateSiswaDTO;
use App\Enum\JenisKelamin;
use App\Enum\StatusSiswa;
use App\Service\SiswaService;
use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use InvalidArgumentException;

#[Route('/akademik/siswa', name: 'akademik_siswa_')]
final class SiswaController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, SiswaService $service, Inertia $inertia): Response
    {
        $query = $request->query->get('q');
        $kelasId = $request->query->get('kelas_id') ? (int) $request->query->get('kelas_id') : null;

        $statusStr = $request->query->get('status');
        $status = $statusStr ? StatusSiswa::tryFrom((string) $statusStr) : null;

        $jkStr = $request->query->get('jenis_kelamin');
        $jenisKelamin = $jkStr ? JenisKelamin::tryFrom((string) $jkStr) : null;

        $isActive = $request->query->has('is_active') && $request->query->get('is_active') !== ''
            ? filter_var($request->query->get('is_active'), FILTER_VALIDATE_BOOLEAN)
            : null;

        $list = $service->getAllSiswa(
            $query !== null ? (string) $query : null,
            $kelasId,
            $status,
            $jenisKelamin,
            $isActive
        );

        $formData = $service->getFormData();
        $statistics = $service->getStatistics();

        return $inertia->render('Akademik/Siswa/Index', [
            'siswaList' => array_map(fn($item) => $item->toArray(), $list),
            'options' => $formData,
            'statistics' => $statistics,
            'filters' => [
                'q' => $query,
                'kelas_id' => $kelasId,
                'status' => $statusStr,
                'jenis_kelamin' => $jkStr,
                'is_active' => $request->query->get('is_active'),
            ],
        ]);
    }

    #[Route('/export', name: 'export', methods: ['GET'])]
    public function export(Request $request, SiswaService $service): Response
    {
        $scope = $request->query->get('scope', 'filtered'); // 'filtered' | 'all'
        $format = $request->query->get('format', 'json'); // 'json' | 'csv'
        $type = $request->query->get('type', 'ringkas'); // 'ringkas' | 'lengkap'

        if ($scope === 'all') {
            $list = $service->getAllSiswa();
        } else {
            $query = $request->query->get('q');
            $kelasId = $request->query->get('kelas_id') ? (int) $request->query->get('kelas_id') : null;
            $statusStr = $request->query->get('status');
            $status = $statusStr ? StatusSiswa::tryFrom((string) $statusStr) : null;
            $jkStr = $request->query->get('jenis_kelamin');
            $jenisKelamin = $jkStr ? JenisKelamin::tryFrom((string) $jkStr) : null;
            $isActive = $request->query->has('is_active') && $request->query->get('is_active') !== ''
                ? filter_var($request->query->get('is_active'), FILTER_VALIDATE_BOOLEAN)
                : null;

            $list = $service->getAllSiswa(
                $query !== null ? (string) $query : null,
                $kelasId,
                $status,
                $jenisKelamin,
                $isActive
            );
        }

        $data = array_map(fn($item) => $item->toArray(), $list);

        if ($format === 'json') {
            return $this->json($data);
        }

        // Generate CSV dengan UTF-8 BOM agar terbaca sempurna di Microsoft Excel
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => sprintf('attachment; filename="Export_Siswa_%s_%s.csv"', $type, date('Ymd_His')),
        ];

        $output = fopen('php://temp', 'r+');
        fputs($output, "\xEF\xBB\xBF");

        if ($type === 'lengkap') {
            fputcsv($output, [
                'No', 'NIS', 'NISN', 'NIK', 'Nama Lengkap', 'Nama Panggilan', 'Nama Arab',
                'Jenis Kelamin', 'Tempat Lahir', 'Tanggal Lahir', 'Agama', 'Kelas', 'Status',
                'Alamat', 'RT', 'RW', 'Kelurahan', 'Kecamatan', 'Kab/Kota', 'Provinsi', 'Kode Pos',
                'No. HP Siswa', 'Email Siswa', 'Nama Ayah', 'Pekerjaan Ayah', 'No. HP Ayah',
                'Nama Ibu', 'Pekerjaan Ibu', 'No. HP Ibu', 'Nama Wali', 'Hubungan Wali', 'No. HP Wali',
                'Anak Ke', 'Jumlah Saudara', 'Tanggal Masuk', 'Asal Sekolah', 'Catatan'
            ], ';');

            foreach ($data as $idx => $s) {
                fputcsv($output, [
                    $idx + 1,
                    $s['nis'],
                    $s['nisn'] ?? '',
                    $s['nik'] ?? '',
                    $s['namaLengkap'],
                    $s['namaPanggilan'] ?? '',
                    $s['namaArab'] ?? '',
                    $s['jenisKelaminLabel'] ?? $s['jenisKelamin'],
                    $s['tempatLahir'] ?? '',
                    $s['tanggalLahir'] ?? '',
                    $s['agama'] ?? '',
                    $s['kelasNama'] ?? '',
                    $s['statusLabel'] ?? $s['status'],
                    $s['alamat'] ?? '',
                    $s['rt'] ?? '',
                    $s['rw'] ?? '',
                    $s['kelurahan'] ?? '',
                    $s['kecamatan'] ?? '',
                    $s['kabupatenKota'] ?? '',
                    $s['provinsi'] ?? '',
                    $s['kodePos'] ?? '',
                    $s['nomorHp'] ?? '',
                    $s['email'] ?? '',
                    $s['namaAyah'] ?? '',
                    $s['pekerjaanAyah'] ?? '',
                    $s['noHpAyah'] ?? '',
                    $s['namaIbu'] ?? '',
                    $s['pekerjaanIbu'] ?? '',
                    $s['noHpIbu'] ?? '',
                    $s['namaWali'] ?? '',
                    $s['hubunganWali'] ?? '',
                    $s['noHpWali'] ?? '',
                    $s['anakKe'] ?? '',
                    $s['jumlahSaudara'] ?? '',
                    $s['tanggalMasuk'] ?? '',
                    $s['asalSekolah'] ?? '',
                    $s['catatan'] ?? '',
                ], ';');
            }
        } else {
            fputcsv($output, [
                'No', 'NIS', 'NISN', 'Nama Lengkap', 'Jenis Kelamin', 'Kelas', 'Status', 'No. HP Siswa', 'Nama Orang Tua', 'No. HP Orang Tua'
            ], ';');

            foreach ($data as $idx => $s) {
                fputcsv($output, [
                    $idx + 1,
                    $s['nis'],
                    $s['nisn'] ?? '',
                    $s['namaLengkap'],
                    $s['jenisKelaminLabel'] ?? $s['jenisKelamin'],
                    $s['kelasNama'] ?? '',
                    $s['statusLabel'] ?? $s['status'],
                    $s['nomorHp'] ?? '',
                    $s['namaAyah'] ?: ($s['namaIbu'] ?: ($s['namaWali'] ?: '')),
                    $s['noHpAyah'] ?: ($s['noHpIbu'] ?: ($s['noHpWali'] ?: '')),
                ], ';');
            }
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return new Response((string) $csvContent, 200, $headers);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id, SiswaService $service, Inertia $inertia): Response
    {
        $siswa = $service->getSiswaById($id);
        if ($siswa === null) {
            $this->addFlash('error', 'Data siswa tidak ditemukan.');
            return $this->redirectToRoute('akademik_siswa_index');
        }

        return $inertia->render('Akademik/Siswa/Show', [
            'siswa' => $siswa->toArray(),
        ]);
    }

    #[Route('', name: 'store', methods: ['POST'])]
    public function store(Request $request, SiswaService $service): Response
    {
        $dto = CreateSiswaDTO::fromRequest($request);

        try {
            $created = $service->createSiswa($dto);
            $this->addFlash('success', sprintf('Data Siswa "%s" (%s) berhasil ditambahkan.', $created->namaLengkap, $created->nis));
        } catch (InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('akademik_siswa_index');
    }

    #[Route('/{id}', name: 'update', requirements: ['id' => '\d+'], methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request, SiswaService $service): Response
    {
        $dto = UpdateSiswaDTO::fromRequest($request);

        try {
            $updated = $service->updateSiswa($id, $dto);
            $this->addFlash('success', sprintf('Data Siswa "%s" berhasil diperbarui.', $updated->namaLengkap));
        } catch (InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('akademik_siswa_index');
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id, SiswaService $service): Response
    {
        try {
            $service->deleteSiswa($id);
            $this->addFlash('success', 'Data Siswa berhasil dihapus.');
        } catch (InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('akademik_siswa_index');
    }

    #[Route('/{id}/toggle-status', name: 'toggle_status', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function toggleStatus(int $id, SiswaService $service): Response
    {
        try {
            $updated = $service->toggleStatus($id);
            $statusText = $updated->isActive ? 'diaktifkan' : 'dinonaktifkan';
            $this->addFlash('success', sprintf('Status siswa "%s" berhasil %s.', $updated->namaLengkap, $statusText));
        } catch (InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('akademik_siswa_index');
    }

    #[Route('/import', name: 'import', methods: ['POST'])]
    public function import(Request $request, SiswaService $service): Response
    {
        $dto = ImportSiswaBatchDTO::fromRequest($request);

        try {
            $result = $service->importSiswa($dto);
            if ($result->totalCreated > 0 || $result->totalUpdated > 0) {
                $this->addFlash('success', $result->getSummaryMessage());
            } elseif ($result->totalSkipped > 0) {
                $this->addFlash('error', $result->getSummaryMessage());
            } else {
                $this->addFlash('error', 'Tidak ada data siswa yang dapat diimpor.');
            }
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Gagal memproses import data siswa: ' . $e->getMessage());
        }

        return $this->redirectToRoute('akademik_siswa_index');
    }
}

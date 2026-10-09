<?php

declare(strict_types=1);

namespace App\Controller\Akademik;

use App\DTO\Siswa\CreateSiswaDTO;
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
}

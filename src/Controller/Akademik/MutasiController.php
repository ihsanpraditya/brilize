<?php

declare(strict_types=1);

namespace App\Controller\Akademik;

use App\DTO\Mutasi\CreateMutasiDTO;
use App\Enum\JenisMutasi;
use App\Service\Akademik\MutasiService;
use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use InvalidArgumentException;

#[Route('/akademik/mutasi', name: 'akademik_mutasi_')]
final class MutasiController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, MutasiService $service, Inertia $inertia): Response
    {
        $query = $request->query->get('q');
        $jenisStr = $request->query->get('jenis');
        $jenis = $jenisStr ? JenisMutasi::tryFrom((string) $jenisStr) : null;
        $tahunId = $request->query->get('tahun_id') ? (int) $request->query->get('tahun_id') : null;
        $kelasId = $request->query->get('kelas_id') ? (int) $request->query->get('kelas_id') : null;

        $mutasiList = $service->getAllMutasi(
            $query !== null ? (string) $query : null,
            $jenis,
            $tahunId,
            $kelasId
        );

        $formData = $service->getFormData();
        $statistics = $service->getStatistics();

        return $inertia->render('Akademik/Mutasi/Index', [
            'mutasiList' => array_map(fn($m) => $m->toArray(), $mutasiList),
            'options' => $formData,
            'statistics' => $statistics,
            'filters' => [
                'q' => $query,
                'jenis' => $jenisStr,
                'tahun_id' => $tahunId,
                'kelas_id' => $kelasId,
            ],
        ]);
    }

    #[Route('', name: 'store', methods: ['POST'])]
    public function store(Request $request, MutasiService $service): Response
    {
        try {
            $dto = CreateMutasiDTO::fromRequest($request);
            $service->createMutasi($dto);
            $this->addFlash('success', 'Riwayat mutasi siswa berhasil dicatat.');
        } catch (InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('akademik_mutasi_index');
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id, MutasiService $service): Response
    {
        try {
            $service->deleteMutasi($id);
            $this->addFlash('success', 'Catatan riwayat mutasi berhasil dihapus.');
        } catch (InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('akademik_mutasi_index');
    }
}

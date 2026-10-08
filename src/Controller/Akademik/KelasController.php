<?php

declare(strict_types=1);

namespace App\Controller\Akademik;

use App\DTO\Kelas\CreateKelasDTO;
use App\DTO\Kelas\UpdateKelasDTO;
use App\Service\KelasService;
use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use InvalidArgumentException;

#[Route('/akademik/kelas', name: 'akademik_kelas_')]
final class KelasController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, KelasService $service, Inertia $inertia): Response
    {
        $query = $request->query->get('q');
        $tahunPelajaranId = $request->query->get('tahun_pelajaran_id') ? (int) $request->query->get('tahun_pelajaran_id') : null;
        $tingkat = $request->query->get('tingkat') ? (int) $request->query->get('tingkat') : null;

        $list = $service->getAllKelas(
            $query !== null ? (string) $query : null,
            $tahunPelajaranId,
            $tingkat
        );

        $formData = $service->getFormData();

        return $inertia->render('Akademik/Kelas/Index', [
            'kelasList' => array_map(fn($item) => $item->toArray(), $list),
            'options' => $formData,
            'filters' => [
                'q' => $query,
                'tahun_pelajaran_id' => $tahunPelajaranId,
                'tingkat' => $tingkat,
            ],
        ]);
    }

    #[Route('', name: 'store', methods: ['POST'])]
    public function store(Request $request, KelasService $service): Response
    {
        $dto = CreateKelasDTO::fromRequest($request);

        try {
            $created = $service->createKelas($dto);
            $this->addFlash('success', sprintf('Kelas "%s" berhasil ditambahkan.', $created->namaKelas));
        } catch (InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('akademik_kelas_index');
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request, KelasService $service): Response
    {
        $dto = UpdateKelasDTO::fromRequest($request);

        try {
            $updated = $service->updateKelas($id, $dto);
            $this->addFlash('success', sprintf('Data Kelas "%s" berhasil diperbarui.', $updated->namaKelas));
        } catch (InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('akademik_kelas_index');
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, KelasService $service): Response
    {
        try {
            $service->deleteKelas($id);
            $this->addFlash('success', 'Data Kelas berhasil dihapus.');
        } catch (InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('akademik_kelas_index');
    }
}

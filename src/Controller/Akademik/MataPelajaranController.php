<?php

declare(strict_types=1);

namespace App\Controller\Akademik;

use App\DTO\MataPelajaran\CreateMataPelajaranDTO;
use App\DTO\MataPelajaran\UpdateMataPelajaranDTO;
use App\Enum\KelompokMapel;
use App\Service\MataPelajaranService;
use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use InvalidArgumentException;

#[Route('/akademik/mata-pelajaran', name: 'akademik_mapel_')]
final class MataPelajaranController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, MataPelajaranService $service, Inertia $inertia): Response
    {
        $query = $request->query->get('q');
        
        $kelompokStr = $request->query->get('kelompok');
        $kelompok = $kelompokStr ? KelompokMapel::tryFrom((string) $kelompokStr) : null;

        $tingkatStr = $request->query->get('tingkat');
        $tingkat = $tingkatStr !== null && $tingkatStr !== '' ? (int) $tingkatStr : null;

        $isActiveStr = $request->query->get('status');
        $isActive = $isActiveStr !== null && $isActiveStr !== '' ? $isActiveStr === '1' || $isActiveStr === 'true' : null;

        $list = $service->getAllMapel(
            $query !== null ? (string) $query : null,
            $kelompok,
            $tingkat,
            $isActive
        );

        $formData = $service->getFormData();

        return $inertia->render('Akademik/MataPelajaran/Index', [
            'mapelList' => array_map(fn($item) => $item->toArray(), $list),
            'options' => $formData,
            'filters' => [
                'q' => $query,
                'kelompok' => $kelompokStr,
                'tingkat' => $tingkatStr,
                'status' => $isActiveStr,
            ],
        ]);
    }

    #[Route('', name: 'store', methods: ['POST'])]
    public function store(Request $request, MataPelajaranService $service): Response
    {
        $dto = CreateMataPelajaranDTO::fromRequest($request);

        try {
            $created = $service->createMapel($dto);
            $this->addFlash('success', sprintf('Mata Pelajaran "%s" (%s) berhasil ditambahkan.', $created->namaMapel, $created->kodeMapel));
        } catch (InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('akademik_mapel_index');
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request, MataPelajaranService $service): Response
    {
        $dto = UpdateMataPelajaranDTO::fromRequest($request);

        try {
            $updated = $service->updateMapel($id, $dto);
            $this->addFlash('success', sprintf('Mata Pelajaran "%s" berhasil diperbarui.', $updated->namaMapel));
        } catch (InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('akademik_mapel_index');
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, MataPelajaranService $service): Response
    {
        try {
            $service->deleteMapel($id);
            $this->addFlash('success', 'Mata Pelajaran berhasil dihapus.');
        } catch (InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('akademik_mapel_index');
    }
}

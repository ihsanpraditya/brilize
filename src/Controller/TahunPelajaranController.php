<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\TahunPelajaran\CreateTahunPelajaranDTO;
use App\DTO\TahunPelajaran\UpdateTahunPelajaranDTO;
use App\Service\TahunPelajaranService;
use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use InvalidArgumentException;

#[Route('/pengaturan/tahun-ajaran', name: 'tahun_pelajaran_')]
final class TahunPelajaranController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(TahunPelajaranService $service, Inertia $inertia): Response
    {
        $list = $service->getAll();
        $active = $service->getActive();

        return $inertia->render('Pengaturan/TahunPelajaran/Index', [
            'tahunPelajaranList' => array_map(fn($item) => $item->toArray(), $list),
            'activeTahunPelajaran' => $active?->toArray(),
        ]);
    }

    #[Route('', name: 'store', methods: ['POST'])]
    public function store(Request $request, TahunPelajaranService $service): Response
    {
        $dto = CreateTahunPelajaranDTO::fromRequest($request);

        try {
            $created = $service->create($dto);
            $this->addFlash('success', sprintf('Tahun Pelajaran %s berhasil ditambahkan.', $created->namaLengkap));
        } catch (InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('tahun_pelajaran_index');
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request, TahunPelajaranService $service): Response
    {
        $dto = UpdateTahunPelajaranDTO::fromRequest($request);

        try {
            $updated = $service->update($id, $dto);
            $this->addFlash('success', sprintf('Tahun Pelajaran %s berhasil diperbarui.', $updated->namaLengkap));
        } catch (InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('tahun_pelajaran_index');
    }

    #[Route('/{id}/set-active', name: 'set_active', methods: ['POST', 'PATCH'])]
    public function setActive(int $id, TahunPelajaranService $service): Response
    {
        try {
            $active = $service->setActive($id);
            $this->addFlash('success', sprintf('Tahun Pelajaran %s berhasil diaktifkan sebagai acuan sistem.', $active->namaLengkap));
        } catch (InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('tahun_pelajaran_index');
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, TahunPelajaranService $service): Response
    {
        try {
            $service->delete($id);
            $this->addFlash('success', 'Data Tahun Pelajaran berhasil dihapus.');
        } catch (InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('tahun_pelajaran_index');
    }
}

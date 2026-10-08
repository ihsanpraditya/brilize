<?php

declare(strict_types=1);

namespace App\Controller\Akademik;

use App\DTO\Pegawai\CreatePegawaiDTO;
use App\DTO\Pegawai\UpdatePegawaiDTO;
use App\Enum\KategoriPegawai;
use App\Enum\StatusKepegawaian;
use App\Service\PegawaiService;
use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use InvalidArgumentException;

#[Route('/akademik/guru', name: 'akademik_guru_')]
final class GuruController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, PegawaiService $service, Inertia $inertia): Response
    {
        $query = $request->query->get('q');
        
        $kategoriStr = $request->query->get('kategori');
        $kategori = $kategoriStr ? KategoriPegawai::tryFrom((string) $kategoriStr) : null;

        $jenisStr = $request->query->get('jenis');

        $statusStr = $request->query->get('status');
        $status = $statusStr ? StatusKepegawaian::tryFrom((string) $statusStr) : null;

        $list = $service->getAllPegawai(
            $query !== null ? (string) $query : null,
            $kategori,
            $jenisStr !== null ? (string) $jenisStr : null,
            $status
        );

        $formData = $service->getFormData();

        return $inertia->render('Akademik/Guru/Index', [
            'pegawaiList' => array_map(fn($item) => $item->toArray(), $list),
            'options' => $formData,
            'filters' => [
                'q' => $query,
                'kategori' => $kategoriStr,
                'jenis' => $jenisStr,
                'status' => $statusStr,
            ],
        ]);
    }

    #[Route('', name: 'store', methods: ['POST'])]
    public function store(Request $request, PegawaiService $service): Response
    {
        $dto = CreatePegawaiDTO::fromRequest($request);

        try {
            $created = $service->createPegawai($dto);
            $this->addFlash('success', sprintf('Data Pegawai / Guru "%s" berhasil ditambahkan.', $created->namaDenganGelar));
        } catch (InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('akademik_guru_index');
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request, PegawaiService $service): Response
    {
        $dto = UpdatePegawaiDTO::fromRequest($request);

        try {
            $updated = $service->updatePegawai($id, $dto);
            $this->addFlash('success', sprintf('Data "%s" berhasil diperbarui.', $updated->namaDenganGelar));
        } catch (InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('akademik_guru_index');
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, PegawaiService $service): Response
    {
        try {
            $service->deletePegawai($id);
            $this->addFlash('success', 'Data Pegawai / Guru berhasil dihapus.');
        } catch (InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('akademik_guru_index');
    }
}

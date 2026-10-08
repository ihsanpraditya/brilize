<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\MataPelajaran\CreateMataPelajaranDTO;
use App\DTO\MataPelajaran\MataPelajaranResponseDTO;
use App\DTO\MataPelajaran\UpdateMataPelajaranDTO;
use App\Entity\Guru;
use App\Entity\MataPelajaran;
use App\Enum\KelompokMapel;
use App\Repository\GuruRepository;
use App\Repository\MataPelajaranRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

class MataPelajaranService
{
    public function __construct(
        private readonly MataPelajaranRepository $mapelRepository,
        private readonly GuruRepository $guruRepository,
        private readonly EntityManagerInterface $em,
    ) {}

    /**
     * @return MataPelajaranResponseDTO[]
     */
    public function getAllMapel(
        ?string $query = null,
        ?KelompokMapel $kelompok = null,
        ?int $tingkat = null,
        ?bool $isActive = null
    ): array {
        $entities = $this->mapelRepository->searchMapel($query, $kelompok, $tingkat, $isActive);

        return array_map(fn(MataPelajaran $m) => MataPelajaranResponseDTO::fromEntity($m), $entities);
    }

    public function getMapelById(int $id): ?MataPelajaranResponseDTO
    {
        $entity = $this->mapelRepository->find($id);

        return $entity ? MataPelajaranResponseDTO::fromEntity($entity) : null;
    }

    public function createMapel(CreateMataPelajaranDTO $dto): MataPelajaranResponseDTO
    {
        $errors = $dto->validate();
        if (!empty($errors)) {
            throw new InvalidArgumentException(reset($errors));
        }

        $existing = $this->mapelRepository->findOneBy(['kodeMapel' => $dto->kodeMapel]);
        if ($existing !== null) {
            throw new InvalidArgumentException(sprintf('Mata Pelajaran dengan kode "%s" sudah ada.', $dto->kodeMapel));
        }

        $mapel = new MataPelajaran();
        $mapel->setKodeMapel($dto->kodeMapel)
            ->setNamaMapel($dto->namaMapel)
            ->setKelompok($dto->kelompok)
            ->setTingkat($dto->tingkat)
            ->setJurusan($dto->jurusan)
            ->setKkm($dto->kkm)
            ->setBebanJamPerMinggu($dto->bebanJamPerMinggu)
            ->setUrutan($dto->urutan)
            ->setKeterangan($dto->keterangan)
            ->setIsActive($dto->isActive);

        // Many-to-Many Guru Pengampu
        foreach ($dto->guruPengampuIds as $guruId) {
            $guru = $this->guruRepository->find($guruId);
            if ($guru !== null) {
                $mapel->addGuruPengampu($guru);
            }
        }

        // Koordinator Mapel
        if ($dto->guruKoordinatorId !== null) {
            $koordinator = $this->guruRepository->find($dto->guruKoordinatorId);
            $mapel->setGuruKoordinator($koordinator);
        } else {
            $mapel->setGuruKoordinator(null);
        }

        $this->mapelRepository->save($mapel, true);

        return MataPelajaranResponseDTO::fromEntity($mapel);
    }

    public function updateMapel(int $id, UpdateMataPelajaranDTO $dto): MataPelajaranResponseDTO
    {
        $errors = $dto->validate();
        if (!empty($errors)) {
            throw new InvalidArgumentException(reset($errors));
        }

        $mapel = $this->mapelRepository->find($id);
        if (!$mapel) {
            throw new InvalidArgumentException('Mata Pelajaran tidak ditemukan.');
        }

        $existing = $this->mapelRepository->findOneBy(['kodeMapel' => $dto->kodeMapel]);
        if ($existing !== null && $existing->getId() !== $id) {
            throw new InvalidArgumentException(sprintf('Kode Mata Pelajaran "%s" sudah digunakan oleh mapel lain.', $dto->kodeMapel));
        }

        $mapel->setKodeMapel($dto->kodeMapel)
            ->setNamaMapel($dto->namaMapel)
            ->setKelompok($dto->kelompok)
            ->setTingkat($dto->tingkat)
            ->setJurusan($dto->jurusan)
            ->setKkm($dto->kkm)
            ->setBebanJamPerMinggu($dto->bebanJamPerMinggu)
            ->setUrutan($dto->urutan)
            ->setKeterangan($dto->keterangan)
            ->setIsActive($dto->isActive);

        // Sinkronisasi Many-to-Many Guru Pengampu
        $currentTeachers = $mapel->getGuruPengampuList()->toArray();
        $targetIds = $dto->guruPengampuIds;

        // Remove teachers not in target
        foreach ($currentTeachers as $currentTeacher) {
            if (!in_array((int) $currentTeacher->getId(), $targetIds, true)) {
                $mapel->removeGuruPengampu($currentTeacher);
            }
        }

        // Add new teachers
        $currentIds = array_map(fn(Guru $g) => (int) $g->getId(), $mapel->getGuruPengampuList()->toArray());
        foreach ($targetIds as $targetId) {
            if (!in_array($targetId, $currentIds, true)) {
                $guru = $this->guruRepository->find($targetId);
                if ($guru !== null) {
                    $mapel->addGuruPengampu($guru);
                }
            }
        }

        // Koordinator Mapel
        if ($dto->guruKoordinatorId !== null) {
            $koordinator = $this->guruRepository->find($dto->guruKoordinatorId);
            $mapel->setGuruKoordinator($koordinator);
        } else {
            $mapel->setGuruKoordinator(null);
        }

        $this->mapelRepository->save($mapel, true);

        return MataPelajaranResponseDTO::fromEntity($mapel);
    }

    public function deleteMapel(int $id): void
    {
        $mapel = $this->mapelRepository->find($id);
        if (!$mapel) {
            throw new InvalidArgumentException('Mata Pelajaran tidak ditemukan.');
        }

        $this->mapelRepository->remove($mapel, true);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormData(): array
    {
        $kelompokOptions = array_map(
            fn(KelompokMapel $k) => [
                'value' => $k->value,
                'label' => $k->label(),
                'badgeClass' => $k->badgeClass(),
            ],
            KelompokMapel::cases()
        );

        $allGuru = $this->guruRepository->searchGuru(isActive: true);
        $guruOptions = array_map(
            fn(Guru $g) => [
                'id' => (int) $g->getId(),
                'nama' => $g->getNamaDenganGelar(),
                'nip' => $g->getNip(),
                'bidangStudi' => $g->getBidangStudiUtama(),
            ],
            $allGuru
        );

        return [
            'kelompok' => $kelompokOptions,
            'guru' => $guruOptions,
            'tingkat' => [10, 11, 12],
        ];
    }
}

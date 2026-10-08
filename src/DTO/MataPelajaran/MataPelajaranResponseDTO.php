<?php

declare(strict_types=1);

namespace App\DTO\MataPelajaran;

use App\Entity\Guru;
use App\Entity\MataPelajaran;

final readonly class MataPelajaranResponseDTO
{
    /**
     * @param array<int, array{id: int, nama: string, nip: ?string}> $guruPengampu
     * @param int[] $guruPengampuIds
     */
    public function __construct(
        public int $id,
        public string $kodeMapel,
        public string $namaMapel,
        public string $kelompok,
        public string $kelompokLabel,
        public string $kelompokBadgeClass,
        public ?int $tingkat,
        public ?string $jurusan,
        public int $kkm,
        public int $bebanJamPerMinggu,
        public array $guruPengampu,
        public array $guruPengampuIds,
        public ?int $guruKoordinatorId,
        public ?string $guruKoordinatorNama,
        public int $urutan,
        public ?string $keterangan,
        public bool $isActive,
        public string $createdAt,
    ) {}

    public static function fromEntity(MataPelajaran $entity): self
    {
        $guruList = [];
        $guruIds = [];

        foreach ($entity->getGuruPengampuList() as $guru) {
            $guruIds[] = (int) $guru->getId();
            $guruList[] = [
                'id' => (int) $guru->getId(),
                'nama' => $guru->getNamaDenganGelar(),
                'nip' => $guru->getNip(),
            ];
        }

        $koordinator = $entity->getGuruKoordinator();

        return new self(
            id: (int) $entity->getId(),
            kodeMapel: $entity->getKodeMapel(),
            namaMapel: $entity->getNamaMapel(),
            kelompok: $entity->getKelompok()->value,
            kelompokLabel: $entity->getKelompok()->label(),
            kelompokBadgeClass: $entity->getKelompok()->badgeClass(),
            tingkat: $entity->getTingkat(),
            jurusan: $entity->getJurusan(),
            kkm: $entity->getKkm(),
            bebanJamPerMinggu: $entity->getBebanJamPerMinggu(),
            guruPengampu: $guruList,
            guruPengampuIds: $guruIds,
            guruKoordinatorId: $koordinator !== null ? (int) $koordinator->getId() : null,
            guruKoordinatorNama: $koordinator?->getNamaDenganGelar(),
            urutan: $entity->getUrutan(),
            keterangan: $entity->getKeterangan(),
            isActive: $entity->isActive(),
            createdAt: $entity->getCreatedAt()->format('Y-m-d H:i:s'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'kodeMapel' => $this->kodeMapel,
            'namaMapel' => $this->namaMapel,
            'kelompok' => $this->kelompok,
            'kelompokLabel' => $this->kelompokLabel,
            'kelompokBadgeClass' => $this->kelompokBadgeClass,
            'tingkat' => $this->tingkat,
            'jurusan' => $this->jurusan,
            'kkm' => $this->kkm,
            'bebanJamPerMinggu' => $this->bebanJamPerMinggu,
            'guruPengampu' => $this->guruPengampu,
            'guruPengampuIds' => $this->guruPengampuIds,
            'guruKoordinatorId' => $this->guruKoordinatorId,
            'guruKoordinatorNama' => $this->guruKoordinatorNama,
            'urutan' => $this->urutan,
            'keterangan' => $this->keterangan,
            'isActive' => $this->isActive,
            'createdAt' => $this->createdAt,
        ];
    }
}

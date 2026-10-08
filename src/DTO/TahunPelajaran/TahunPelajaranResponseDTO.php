<?php

declare(strict_types=1);

namespace App\DTO\TahunPelajaran;

use App\Entity\TahunPelajaran;

final readonly class TahunPelajaranResponseDTO
{
    public function __construct(
        public int $id,
        public string $tahun,
        public string $semester,
        public string $semesterLabel,
        public bool $isActive,
        public ?string $tanggalMulai,
        public ?string $tanggalSelesai,
        public string $namaLengkap,
        public string $createdAt,
    ) {}

    public static function fromEntity(TahunPelajaran $entity): self
    {
        return new self(
            id: (int) $entity->getId(),
            tahun: $entity->getTahun(),
            semester: $entity->getSemester()->value,
            semesterLabel: $entity->getSemester()->label(),
            isActive: $entity->isActive(),
            tanggalMulai: $entity->getTanggalMulai()?->format('Y-m-d'),
            tanggalSelesai: $entity->getTanggalSelesai()?->format('Y-m-d'),
            namaLengkap: $entity->getNamaLengkap(),
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
            'tahun' => $this->tahun,
            'semester' => $this->semester,
            'semesterLabel' => $this->semesterLabel,
            'isActive' => $this->isActive,
            'tanggalMulai' => $this->tanggalMulai,
            'tanggalSelesai' => $this->tanggalSelesai,
            'namaLengkap' => $this->namaLengkap,
            'createdAt' => $this->createdAt,
        ];
    }
}

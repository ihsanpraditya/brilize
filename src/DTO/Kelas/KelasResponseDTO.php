<?php

declare(strict_types=1);

namespace App\DTO\Kelas;

use App\Entity\Kelas;

final readonly class KelasResponseDTO
{
    public function __construct(
        public int $id,
        public string $namaKelas,
        public int $tingkat,
        public ?string $jurusan,
        public ?int $tahunPelajaranId,
        public ?string $tahunPelajaranNama,
        public ?int $waliKelasId,
        public ?string $waliKelasNama,
        public int $kapasitas,
        public ?string $keterangan,
        public bool $isActive,
        public string $createdAt,
    ) {}

    public static function fromEntity(Kelas $entity): self
    {
        $tp = $entity->getTahunPelajaran();
        $wk = $entity->getWaliKelas();

        return new self(
            id: (int) $entity->getId(),
            namaKelas: $entity->getNamaKelas(),
            tingkat: $entity->getTingkat(),
            jurusan: $entity->getJurusan(),
            tahunPelajaranId: $tp !== null ? (int) $tp->getId() : null,
            tahunPelajaranNama: $tp?->getNamaLengkap(),
            waliKelasId: $wk !== null ? (int) $wk->getId() : null,
            waliKelasNama: $wk?->getName(),
            kapasitas: $entity->getKapasitas(),
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
            'namaKelas' => $this->namaKelas,
            'tingkat' => $this->tingkat,
            'jurusan' => $this->jurusan,
            'tahunPelajaranId' => $this->tahunPelajaranId,
            'tahunPelajaranNama' => $this->tahunPelajaranNama,
            'waliKelasId' => $this->waliKelasId,
            'waliKelasNama' => $this->waliKelasNama,
            'kapasitas' => $this->kapasitas,
            'keterangan' => $this->keterangan,
            'isActive' => $this->isActive,
            'createdAt' => $this->createdAt,
        ];
    }
}

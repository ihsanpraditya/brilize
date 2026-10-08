<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\TahunPelajaran\CreateTahunPelajaranDTO;
use App\DTO\TahunPelajaran\TahunPelajaranResponseDTO;
use App\DTO\TahunPelajaran\UpdateTahunPelajaranDTO;
use App\Entity\TahunPelajaran;
use App\Repository\TahunPelajaranRepository;
use DateTimeImmutable;
use InvalidArgumentException;

final readonly class TahunPelajaranService
{
    public function __construct(
        private TahunPelajaranRepository $repository,
    ) {}

    /**
     * @return list<TahunPelajaranResponseDTO>
     */
    public function getAll(): array
    {
        $list = $this->repository->findBy([], ['tahun' => 'DESC', 'semester' => 'ASC']);

        return array_map(fn(TahunPelajaran $tp) => TahunPelajaranResponseDTO::fromEntity($tp), $list);
    }

    public function getById(int $id): ?TahunPelajaranResponseDTO
    {
        $entity = $this->repository->find($id);

        return $entity !== null ? TahunPelajaranResponseDTO::fromEntity($entity) : null;
    }

    public function getActive(): ?TahunPelajaranResponseDTO
    {
        $entity = $this->repository->findActive();

        return $entity !== null ? TahunPelajaranResponseDTO::fromEntity($entity) : null;
    }

    public function create(CreateTahunPelajaranDTO $dto): TahunPelajaranResponseDTO
    {
        $errors = $dto->validate();
        if (!empty($errors)) {
            throw new InvalidArgumentException(reset($errors));
        }

        $existing = $this->repository->findOneBy([
            'tahun' => $dto->tahun,
            'semester' => $dto->semester,
        ]);

        if ($existing !== null) {
            throw new InvalidArgumentException(sprintf(
                'Tahun Pelajaran %s Semester %s sudah terdaftar.',
                $dto->tahun,
                $dto->semester->label()
            ));
        }

        $entity = new TahunPelajaran();
        $entity->setTahun($dto->tahun);
        $entity->setSemester($dto->semester);

        if ($dto->tanggalMulai) {
            $entity->setTanggalMulai(new DateTimeImmutable($dto->tanggalMulai));
        }
        if ($dto->tanggalSelesai) {
            $entity->setTanggalSelesai(new DateTimeImmutable($dto->tanggalSelesai));
        }

        // Jika ini adalah data pertama di sistem atau diset aktif
        $totalCount = count($this->repository->findAll());
        if ($dto->isActive || $totalCount === 0) {
            $this->repository->setActive($entity);
        } else {
            $this->repository->save($entity, true);
        }

        return TahunPelajaranResponseDTO::fromEntity($entity);
    }

    public function update(int $id, UpdateTahunPelajaranDTO $dto): TahunPelajaranResponseDTO
    {
        $entity = $this->repository->find($id);
        if ($entity === null) {
            throw new InvalidArgumentException('Data Tahun Pelajaran tidak ditemukan.');
        }

        $errors = $dto->validate();
        if (!empty($errors)) {
            throw new InvalidArgumentException(reset($errors));
        }

        $existing = $this->repository->findOneBy([
            'tahun' => $dto->tahun,
            'semester' => $dto->semester,
        ]);

        if ($existing !== null && $existing->getId() !== $entity->getId()) {
            throw new InvalidArgumentException(sprintf(
                'Tahun Pelajaran %s Semester %s sudah digunakan pada entitas lain.',
                $dto->tahun,
                $dto->semester->label()
            ));
        }

        $entity->setTahun($dto->tahun);
        $entity->setSemester($dto->semester);

        $entity->setTanggalMulai($dto->tanggalMulai ? new DateTimeImmutable($dto->tanggalMulai) : null);
        $entity->setTanggalSelesai($dto->tanggalSelesai ? new DateTimeImmutable($dto->tanggalSelesai) : null);

        $this->repository->save($entity, true);

        return TahunPelajaranResponseDTO::fromEntity($entity);
    }

    public function setActive(int $id): TahunPelajaranResponseDTO
    {
        $entity = $this->repository->find($id);
        if ($entity === null) {
            throw new InvalidArgumentException('Data Tahun Pelajaran tidak ditemukan.');
        }

        $this->repository->setActive($entity);

        return TahunPelajaranResponseDTO::fromEntity($entity);
    }

    public function delete(int $id): void
    {
        $entity = $this->repository->find($id);
        if ($entity === null) {
            return;
        }

        if ($entity->isActive()) {
            throw new InvalidArgumentException('Tidak dapat menghapus Tahun Pelajaran yang sedang aktif.');
        }

        $this->repository->remove($entity, true);
    }
}

<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Siswa;
use App\Enum\JenisKelamin;
use App\Enum\StatusSiswa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Siswa>
 */
class SiswaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Siswa::class);
    }

    public function save(Siswa $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Siswa $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Cari siswa berdasarkan NIS
     */
    public function findOneByNis(string $nis): ?Siswa
    {
        return $this->findOneBy(['nis' => trim($nis)]);
    }

    /**
     * Cari siswa berdasarkan NISN
     */
    public function findOneByNisn(string $nisn): ?Siswa
    {
        return $this->findOneBy(['nisn' => trim($nisn)]);
    }

    /**
     * Pencarian siswa dengan filter kelas, status, jenis kelamin, dan keyword
     *
     * @return Siswa[]
     */
    public function searchSiswa(
        ?string $query = null,
        ?int $kelasId = null,
        ?StatusSiswa $status = null,
        ?JenisKelamin $jenisKelamin = null,
        ?bool $isActive = null
    ): array {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.kelas', 'k')
            ->leftJoin('s.user', 'u')
            ->addSelect('k', 'u')
            ->orderBy('s.namaLengkap', 'ASC');

        if ($query) {
            $qb->andWhere('LOWER(s.namaLengkap) LIKE :q OR s.nis LIKE :q OR s.nisn LIKE :q OR s.nik LIKE :q OR LOWER(s.namaPanggilan) LIKE :q OR LOWER(s.namaArab) LIKE :q OR LOWER(s.email) LIKE :q')
                ->setParameter('q', '%' . strtolower(trim($query)) . '%');
        }

        if ($kelasId !== null) {
            $qb->andWhere('k.id = :kelasId')
                ->setParameter('kelasId', $kelasId);
        }

        if ($status !== null) {
            $qb->andWhere('s.status = :status')
                ->setParameter('status', $status);
        }

        if ($jenisKelamin !== null) {
            $qb->andWhere('s.jenisKelamin = :jenisKelamin')
                ->setParameter('jenisKelamin', $jenisKelamin);
        }

        if ($isActive !== null) {
            $qb->andWhere('s.isActive = :isActive')
                ->setParameter('isActive', $isActive);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Hitung total siswa per status
     *
     * @return array<string, int>
     */
    public function countByStatus(): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s.status, COUNT(s.id) as total')
            ->groupBy('s.status');

        $results = $qb->getQuery()->getArrayResult();
        $counts = [];

        foreach ($results as $row) {
            $statusKey = $row['status'] instanceof StatusSiswa ? $row['status']->value : (string) $row['status'];
            $counts[$statusKey] = (int) $row['total'];
        }

        return $counts;
    }
}

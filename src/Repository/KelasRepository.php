<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Kelas;
use App\Entity\TahunPelajaran;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Kelas>
 */
class KelasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Kelas::class);
    }

    public function save(Kelas $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Kelas $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Pencarian kelas dengan eager loading relasi untuk mencegah N+1 queries
     *
     * @return Kelas[]
     */
    public function searchKelas(
        ?string $query = null,
        ?int $tahunPelajaranId = null,
        ?int $tingkat = null,
        ?bool $isActive = null
    ): array {
        $qb = $this->createQueryBuilder('k')
            ->leftJoin('k.tahunPelajaran', 'tp')
            ->addSelect('tp')
            ->leftJoin('k.waliKelas', 'wk')
            ->addSelect('wk')
            ->orderBy('k.tingkat', 'ASC')
            ->addOrderBy('k.namaKelas', 'ASC');

        if ($query) {
            $qb->andWhere('LOWER(k.namaKelas) LIKE :q OR LOWER(k.jurusan) LIKE :q OR LOWER(wk.name) LIKE :q')
               ->setParameter('q', '%' . strtolower(trim($query)) . '%');
        }

        if ($tahunPelajaranId !== null) {
            $qb->andWhere('tp.id = :tahunPelajaranId')
               ->setParameter('tahunPelajaranId', $tahunPelajaranId);
        }

        if ($tingkat !== null) {
            $qb->andWhere('k.tingkat = :tingkat')
               ->setParameter('tingkat', $tingkat);
        }

        if ($isActive !== null) {
            $qb->andWhere('k.isActive = :isActive')
               ->setParameter('isActive', $isActive);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Kelas[]
     */
    public function findByTahunPelajaran(TahunPelajaran $tahunPelajaran): array
    {
        return $this->createQueryBuilder('k')
            ->leftJoin('k.waliKelas', 'wk')
            ->addSelect('wk')
            ->where('k.tahunPelajaran = :tp')
            ->andWhere('k.isActive = true')
            ->setParameter('tp', $tahunPelajaran)
            ->orderBy('k.tingkat', 'ASC')
            ->addOrderBy('k.namaKelas', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MataPelajaran;
use App\Enum\KelompokMapel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MataPelajaran>
 */
class MataPelajaranRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MataPelajaran::class);
    }

    public function save(MataPelajaran $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MataPelajaran $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Pencarian mata pelajaran dengan eager loading relasi Guru dan Pegawai
     *
     * @return MataPelajaran[]
     */
    public function searchMapel(
        ?string $query = null,
        ?KelompokMapel $kelompok = null,
        ?int $tingkat = null,
        ?bool $isActive = null
    ): array {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.guruPengampuList', 'g')
            ->addSelect('g')
            ->leftJoin('g.pegawai', 'p')
            ->addSelect('p')
            ->leftJoin('m.guruKoordinator', 'gk')
            ->addSelect('gk')
            ->leftJoin('gk.pegawai', 'gkp')
            ->addSelect('gkp')
            ->orderBy('m.kelompok', 'ASC')
            ->addOrderBy('m.urutan', 'ASC')
            ->addOrderBy('m.namaMapel', 'ASC');

        if ($query) {
            $qb->andWhere('LOWER(m.namaMapel) LIKE :q OR UPPER(m.kodeMapel) LIKE :q OR LOWER(m.jurusan) LIKE :q')
               ->setParameter('q', '%' . strtolower(trim($query)) . '%');
        }

        if ($kelompok !== null) {
            $qb->andWhere('m.kelompok = :kelompok')
               ->setParameter('kelompok', $kelompok);
        }

        if ($tingkat !== null) {
            $qb->andWhere('m.tingkat = :tingkat OR m.tingkat IS NULL')
               ->setParameter('tingkat', $tingkat);
        }

        if ($isActive !== null) {
            $qb->andWhere('m.isActive = :isActive')
               ->setParameter('isActive', $isActive);
        }

        return $qb->getQuery()->getResult();
    }
}

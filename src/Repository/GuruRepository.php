<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Guru;
use App\Enum\StatusKepegawaian;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Guru>
 */
class GuruRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Guru::class);
    }

    public function save(Guru $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Guru $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Pencarian tenaga pendidik (guru) dengan eager loading data Pegawai dan User
     *
     * @return Guru[]
     */
    public function searchGuru(
        ?string $query = null,
        ?StatusKepegawaian $statusKepegawaian = null,
        ?bool $isSertifikasi = null,
        ?bool $isActive = null
    ): array {
        $qb = $this->createQueryBuilder('g')
            ->innerJoin('g.pegawai', 'p')
            ->addSelect('p')
            ->leftJoin('p.user', 'u')
            ->addSelect('u')
            ->orderBy('p.namaLengkap', 'ASC');

        if ($query) {
            $qb->andWhere('LOWER(p.namaLengkap) LIKE :q OR p.nip LIKE :q OR p.nuptk LIKE :q OR LOWER(g.bidangStudiUtama) LIKE :q')
               ->setParameter('q', '%' . strtolower(trim($query)) . '%');
        }

        if ($statusKepegawaian !== null) {
            $qb->andWhere('p.statusKepegawaian = :statusPegawai')
               ->setParameter('statusPegawai', $statusKepegawaian);
        }

        if ($isSertifikasi !== null) {
            $qb->andWhere('g.isSertifikasi = :sertifikasi')
               ->setParameter('sertifikasi', $isSertifikasi);
        }

        if ($isActive !== null) {
            $qb->andWhere('p.isActive = :isActive')
               ->setParameter('isActive', $isActive);
        }

        return $qb->getQuery()->getResult();
    }
}

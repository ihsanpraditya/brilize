<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Guru;
use App\Enum\JenisKepegawaian;
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
     * Pencarian guru/PTK dengan eager loading relasi User
     *
     * @return Guru[]
     */
    public function searchGuru(
        ?string $query = null,
        ?StatusKepegawaian $statusKepegawaian = null,
        ?JenisKepegawaian $jenisKepegawaian = null,
        ?bool $isActive = null
    ): array {
        $qb = $this->createQueryBuilder('g')
            ->leftJoin('g.user', 'u')
            ->addSelect('u')
            ->orderBy('g.namaLengkap', 'ASC');

        if ($query) {
            $qb->andWhere('LOWER(g.namaLengkap) LIKE :q OR g.nip LIKE :q OR g.nuptk LIKE :q OR g.nik LIKE :q OR LOWER(g.email) LIKE :q')
               ->setParameter('q', '%' . strtolower(trim($query)) . '%');
        }

        if ($statusKepegawaian !== null) {
            $qb->andWhere('g.statusKepegawaian = :statusPegawai')
               ->setParameter('statusPegawai', $statusKepegawaian);
        }

        if ($jenisKepegawaian !== null) {
            $qb->andWhere('g.jenisKepegawaian = :jenisPegawai')
               ->setParameter('jenisPegawai', $jenisKepegawaian);
        }

        if ($isActive !== null) {
            $qb->andWhere('g.isActive = :isActive')
               ->setParameter('isActive', $isActive);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Mencari guru berdasarkan NIP atau NUPTK
     */
    public function findByIdentifier(string $identifier): ?Guru
    {
        $trimmed = trim($identifier);

        return $this->createQueryBuilder('g')
            ->leftJoin('g.user', 'u')
            ->addSelect('u')
            ->where('g.nip = :id')
            ->orWhere('g.nuptk = :id')
            ->orWhere('g.nik = :id')
            ->setParameter('id', $trimmed)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

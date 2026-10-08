<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TahunPelajaran;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TahunPelajaran>
 */
class TahunPelajaranRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TahunPelajaran::class);
    }

    public function save(TahunPelajaran $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TahunPelajaran $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Mendapatkan Tahun Pelajaran yang sedang aktif
     */
    public function findActive(): ?TahunPelajaran
    {
        return $this->findOneBy(['isActive' => true]);
    }

    /**
     * Mengaktifkan satu Tahun Pelajaran dan menonaktifkan yang lain secara atomik
     */
    public function setActive(TahunPelajaran $target): void
    {
        $em = $this->getEntityManager();

        // Nonaktifkan semua tahun pelajaran
        $this->createQueryBuilder('tp')
            ->update()
            ->set('tp.isActive', ':false')
            ->setParameter('false', false)
            ->getQuery()
            ->execute();

        // Aktifkan target yang dipilih
        $target->setIsActive(true);
        $em->persist($target);
        $em->flush();
    }
}

<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MutasiSiswa;
use App\Enum\JenisMutasi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MutasiSiswa>
 */
class MutasiSiswaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MutasiSiswa::class);
    }

    public function save(MutasiSiswa $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MutasiSiswa $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return MutasiSiswa[]
     */
    public function searchMutasi(
        ?string $query = null,
        ?JenisMutasi $jenis = null,
        ?int $tahunId = null,
        ?int $kelasId = null
    ): array {
        $qb = $this->createQueryBuilder('m')
            ->innerJoin('m.siswa', 's')
            ->innerJoin('m.tahunPelajaran', 'tp')
            ->leftJoin('m.kelasAsal', 'ka')
            ->leftJoin('m.kelasTujuan', 'kt')
            ->addSelect('s', 'tp', 'ka', 'kt')
            ->orderBy('m.tanggalMutasi', 'DESC')
            ->addOrderBy('m.createdAt', 'DESC');

        if ($query) {
            $qb->andWhere(
                'LOWER(s.namaLengkap) LIKE :q OR s.nis LIKE :q OR s.nisn LIKE :q OR LOWER(m.nomorSurat) LIKE :q OR LOWER(m.sekolahAsalTujuan) LIKE :q'
            )->setParameter('q', '%' . strtolower(trim($query)) . '%');
        }

        if ($jenis !== null) {
            $qb->andWhere('m.jenis = :jenis')
               ->setParameter('jenis', $jenis);
        }

        if ($tahunId !== null) {
            $qb->andWhere('tp.id = :tahunId')
               ->setParameter('tahunId', $tahunId);
        }

        if ($kelasId !== null) {
            $qb->andWhere('ka.id = :kelasId OR kt.id = :kelasId')
               ->setParameter('kelasId', $kelasId);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return MutasiSiswa[]
     */
    public function findBySiswa(int $siswaId): array
    {
        return $this->createQueryBuilder('m')
            ->innerJoin('m.tahunPelajaran', 'tp')
            ->leftJoin('m.kelasAsal', 'ka')
            ->leftJoin('m.kelasTujuan', 'kt')
            ->addSelect('tp', 'ka', 'kt')
            ->where('m.siswa = :siswaId')
            ->setParameter('siswaId', $siswaId)
            ->orderBy('m.tanggalMutasi', 'DESC')
            ->getQuery()
            ->getResult();
    }
}

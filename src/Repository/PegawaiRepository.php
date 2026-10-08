<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Pegawai;
use App\Enum\JenisPegawai;
use App\Enum\KategoriPegawai;
use App\Enum\StatusKepegawaian;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pegawai>
 */
class PegawaiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pegawai::class);
    }

    public function save(Pegawai $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Pegawai $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Pencarian seluruh staf pegawai & guru dengan eager load User
     *
     * @return Pegawai[]
     */
    public function searchPegawai(
        ?string $query = null,
        ?KategoriPegawai $kategori = null,
        ?JenisPegawai $jenis = null,
        ?StatusKepegawaian $status = null,
        ?bool $isActive = null
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->addSelect('u')
            ->orderBy('p.namaLengkap', 'ASC');

        if ($query) {
            $qb->andWhere('LOWER(p.namaLengkap) LIKE :q OR p.nip LIKE :q OR p.nuptk LIKE :q OR p.nik LIKE :q OR LOWER(p.email) LIKE :q OR LOWER(p.jabatan) LIKE :q')
               ->setParameter('q', '%' . strtolower(trim($query)) . '%');
        }

        if ($kategori !== null) {
            $qb->andWhere('p.kategoriPegawai = :kategori')
               ->setParameter('kategori', $kategori);
        }

        if ($jenis !== null) {
            $qb->andWhere('p.jenisPegawai = :jenis')
               ->setParameter('jenis', $jenis);
        }

        if ($status !== null) {
            $qb->andWhere('p.statusKepegawaian = :status')
               ->setParameter('status', $status);
        }

        if ($isActive !== null) {
            $qb->andWhere('p.isActive = :isActive')
               ->setParameter('isActive', $isActive);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Mendapatkan ringkasan statistik kepegawaian untuk dashboard
     * @return array<string, int>
     */
    public function getStatistikPegawai(): array
    {
        $total = $this->count(['isActive' => true]);
        $totalPendidik = $this->count(['kategoriPegawai' => KategoriPegawai::PENDIDIK, 'isActive' => true]);
        $totalTendik = $this->count(['kategoriPegawai' => KategoriPegawai::TENAGA_KEPENDIDIKAN, 'isActive' => true]);
        $totalPNS = $this->count(['statusKepegawaian' => StatusKepegawaian::PNS, 'isActive' => true]);
        $totalPPPK = $this->count(['statusKepegawaian' => StatusKepegawaian::PPPK, 'isActive' => true]);
        $totalHonorer = $this->count(['statusKepegawaian' => StatusKepegawaian::HONORER, 'isActive' => true]);

        return [
            'total' => $total,
            'totalPendidik' => $totalPendidik,
            'totalTendik' => $totalTendik,
            'totalPNS' => $totalPNS,
            'totalPPPK' => $totalPPPK,
            'totalHonorer' => $totalHonorer,
        ];
    }
}

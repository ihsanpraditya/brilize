<?php

declare(strict_types=1);

namespace App\Service\Akademik;

use App\DTO\Mutasi\CreateMutasiDTO;
use App\DTO\Mutasi\MutasiResponseDTO;
use App\Entity\MutasiSiswa;
use App\Entity\Siswa;
use App\Enum\JenisMutasi;
use App\Enum\StatusSiswa;
use App\Repository\KelasRepository;
use App\Repository\MutasiSiswaRepository;
use App\Repository\SiswaRepository;
use App\Repository\TahunPelajaranRepository;
use InvalidArgumentException;

final readonly class MutasiService
{
    public function __construct(
        private MutasiSiswaRepository $mutasiRepository,
        private SiswaRepository $siswaRepository,
        private KelasRepository $kelasRepository,
        private TahunPelajaranRepository $tahunPelajaranRepository,
    ) {}

    /**
     * @return list<MutasiResponseDTO>
     */
    public function getAllMutasi(
        ?string $query = null,
        ?JenisMutasi $jenis = null,
        ?int $tahunId = null,
        ?int $kelasId = null
    ): array {
        $list = $this->mutasiRepository->searchMutasi($query, $jenis, $tahunId, $kelasId);

        return array_map(fn(MutasiSiswa $m) => MutasiResponseDTO::fromEntity($m), $list);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormData(): array
    {
        $activeTahun = $this->tahunPelajaranRepository->findOneBy(['isActive' => true]);
        $tahunList = $this->tahunPelajaranRepository->findBy([], ['tahun' => 'DESC', 'semester' => 'DESC']);
        $kelasList = $this->kelasRepository->findBy(['isActive' => true], ['tingkat' => 'ASC', 'namaKelas' => 'ASC']);
        $siswaList = $this->siswaRepository->findBy([], ['namaLengkap' => 'ASC']);

        return [
            'activeTahunId' => $activeTahun?->getId(),
            'tahunOptions' => array_map(fn($t) => [
                'id' => $t->getId(),
                'nama' => $t->getNamaLengkap(),
                'isActive' => $t->isActive(),
            ], $tahunList),
            'kelasOptions' => array_map(fn($k) => [
                'id' => $k->getId(),
                'nama' => $k->getNamaKelas(),
                'tingkat' => $k->getTingkat(),
                'jurusan' => $k->getJurusan(),
            ], $kelasList),
            'siswaOptions' => array_map(fn(Siswa $s) => [
                'id' => $s->getId(),
                'nis' => $s->getNis(),
                'nisn' => $s->getNisn(),
                'nama' => $s->getNamaLengkap(),
                'kelasId' => $s->getKelas()?->getId(),
                'kelasNama' => $s->getKelas()?->getNamaKelas(),
                'status' => $s->getStatus()->value,
                'statusLabel' => $s->getStatus()->label(),
            ], $siswaList),
            'jenisOptions' => array_map(fn(JenisMutasi $j) => [
                'value' => $j->value,
                'label' => $j->label(),
                'badge' => $j->badgeClass(),
            ], JenisMutasi::cases()),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function getStatistics(): array
    {
        $all = $this->mutasiRepository->findAll();
        $total = count($all);
        $totalPindahRombel = 0;
        $totalMutasiMasuk = 0;
        $totalMutasiKeluar = 0;
        $totalLulus = 0;

        foreach ($all as $item) {
            match ($item->getJenis()) {
                JenisMutasi::NAIK_KELAS, JenisMutasi::PINDAH_KELAS, JenisMutasi::TINGGAL_KELAS => $totalPindahRombel++,
                JenisMutasi::MASUK => $totalMutasiMasuk++,
                JenisMutasi::KELUAR, JenisMutasi::DROPOUT => $totalMutasiKeluar++,
                JenisMutasi::LULUS => $totalLulus++,
            };
        }

        return [
            'total' => $total,
            'totalPindahRombel' => $totalPindahRombel,
            'totalMutasiMasuk' => $totalMutasiMasuk,
            'totalMutasiKeluar' => $totalMutasiKeluar,
            'totalLulus' => $totalLulus,
        ];
    }

    public function createMutasi(CreateMutasiDTO $dto): MutasiResponseDTO
    {
        $siswa = $this->siswaRepository->find($dto->siswaId);
        if ($siswa === null) {
            throw new InvalidArgumentException('Siswa tidak ditemukan.');
        }

        $tahun = $this->tahunPelajaranRepository->find($dto->tahunPelajaranId);
        if ($tahun === null) {
            throw new InvalidArgumentException('Tahun pelajaran tidak ditemukan.');
        }

        $kelasAsal = $dto->kelasAsalId ? $this->kelasRepository->find($dto->kelasAsalId) : $siswa->getKelas();
        $kelasTujuan = $dto->kelasTujuanId ? $this->kelasRepository->find($dto->kelasTujuanId) : null;

        // Validasi logika mutasi
        if (in_array($dto->jenis, [JenisMutasi::NAIK_KELAS, JenisMutasi::PINDAH_KELAS, JenisMutasi::TINGGAL_KELAS], true)) {
            if ($kelasTujuan === null) {
                throw new InvalidArgumentException('Kelas tujuan wajib dipilih untuk jenis mutasi ini.');
            }
        }

        $mutasi = new MutasiSiswa();
        $mutasi->setSiswa($siswa);
        $mutasi->setTahunPelajaran($tahun);
        $mutasi->setJenis($dto->jenis);
        $mutasi->setKelasAsal($kelasAsal);
        $mutasi->setKelasTujuan($kelasTujuan);
        $mutasi->setTanggalMutasi(new \DateTimeImmutable($dto->tanggalMutasi));
        $mutasi->setNomorSurat($dto->nomorSurat);
        $mutasi->setSekolahAsalTujuan($dto->sekolahAsalTujuan);
        $mutasi->setAlasan($dto->alasan);

        // Sinkronisasi status dan kelas siswa
        switch ($dto->jenis) {
            case JenisMutasi::NAIK_KELAS:
            case JenisMutasi::PINDAH_KELAS:
            case JenisMutasi::TINGGAL_KELAS:
                $siswa->setKelas($kelasTujuan);
                $siswa->setStatus(StatusSiswa::AKTIF);
                break;

            case JenisMutasi::MASUK:
                if ($kelasTujuan !== null) {
                    $siswa->setKelas($kelasTujuan);
                }
                $siswa->setStatus(StatusSiswa::AKTIF);
                if ($dto->sekolahAsalTujuan) {
                    $siswa->setAsalSekolah($dto->sekolahAsalTujuan);
                }
                break;

            case JenisMutasi::KELUAR:
                $siswa->setStatus(StatusSiswa::MUTASI);
                $siswa->setKelas(null);
                break;

            case JenisMutasi::LULUS:
                $siswa->setStatus(StatusSiswa::LULUS);
                break;

            case JenisMutasi::DROPOUT:
                $siswa->setStatus(StatusSiswa::KELUAR);
                $siswa->setKelas(null);
                break;
        }

        $this->siswaRepository->save($siswa, false);
        $this->mutasiRepository->save($mutasi, true);

        return MutasiResponseDTO::fromEntity($mutasi);
    }

    public function deleteMutasi(int $id): void
    {
        $mutasi = $this->mutasiRepository->find($id);
        if ($mutasi === null) {
            throw new InvalidArgumentException('Data mutasi tidak ditemukan.');
        }

        $this->mutasiRepository->remove($mutasi, true);
    }
}

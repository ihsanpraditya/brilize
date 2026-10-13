<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Kelas\CreateKelasDTO;
use App\DTO\Kelas\ImportKelasBatchDTO;
use App\DTO\Kelas\ImportKelasResultDTO;
use App\DTO\Kelas\KelasResponseDTO;
use App\DTO\Kelas\UpdateKelasDTO;
use App\Entity\Kelas;
use App\Enum\UserRole;
use App\Repository\KelasRepository;
use App\Repository\TahunPelajaranRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

final readonly class KelasService
{
    public function __construct(
        private KelasRepository $kelasRepository,
        private TahunPelajaranRepository $tahunPelajaranRepository,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * @return list<KelasResponseDTO>
     */
    public function getAllKelas(
        ?string $query = null,
        ?int $tahunPelajaranId = null,
        ?int $tingkat = null,
        ?bool $isActive = null
    ): array {
        $list = $this->kelasRepository->searchKelas($query, $tahunPelajaranId, $tingkat, $isActive);

        return array_map(fn(Kelas $k) => KelasResponseDTO::fromEntity($k), $list);
    }

    public function getKelasById(int $id): ?KelasResponseDTO
    {
        $entity = $this->kelasRepository->find($id);

        return $entity !== null ? KelasResponseDTO::fromEntity($entity) : null;
    }

    public function createKelas(CreateKelasDTO $dto): KelasResponseDTO
    {
        $errors = $dto->validate();
        if (!empty($errors)) {
            throw new InvalidArgumentException(reset($errors));
        }

        // Tentukan Tahun Pelajaran (atau default ke Tahun Pelajaran aktif)
        $tahunPelajaran = null;
        if ($dto->tahunPelajaranId !== null) {
            $tahunPelajaran = $this->tahunPelajaranRepository->find($dto->tahunPelajaranId);
        } else {
            $tahunPelajaran = $this->tahunPelajaranRepository->findActive();
        }

        // Cek duplikasi nama kelas di tahun ajaran yang sama
        $existing = $this->kelasRepository->findOneBy([
            'namaKelas' => $dto->namaKelas,
            'tahunPelajaran' => $tahunPelajaran,
        ]);

        if ($existing !== null) {
            throw new InvalidArgumentException(sprintf(
                'Kelas dengan nama "%s" sudah terdaftar untuk %s.',
                $dto->namaKelas,
                $tahunPelajaran ? $tahunPelajaran->getNamaLengkap() : 'tahun pelajaran ini'
            ));
        }

        $waliKelas = null;
        if ($dto->waliKelasId !== null) {
            $waliKelas = $this->userRepository->find($dto->waliKelasId);
        }

        $kelas = new Kelas();
        $kelas->setNamaKelas($dto->namaKelas);
        $kelas->setTingkat($dto->tingkat);
        $kelas->setJurusan($dto->jurusan);
        $kelas->setTahunPelajaran($tahunPelajaran);
        $kelas->setWaliKelas($waliKelas);
        $kelas->setKapasitas($dto->kapasitas);
        $kelas->setKeterangan($dto->keterangan);
        $kelas->setIsActive($dto->isActive);

        $this->kelasRepository->save($kelas, true);

        return KelasResponseDTO::fromEntity($kelas);
    }

    public function updateKelas(int $id, UpdateKelasDTO $dto): KelasResponseDTO
    {
        $kelas = $this->kelasRepository->find($id);
        if ($kelas === null) {
            throw new InvalidArgumentException('Data Kelas tidak ditemukan.');
        }

        $errors = $dto->validate();
        if (!empty($errors)) {
            throw new InvalidArgumentException(reset($errors));
        }

        $tahunPelajaran = null;
        if ($dto->tahunPelajaranId !== null) {
            $tahunPelajaran = $this->tahunPelajaranRepository->find($dto->tahunPelajaranId);
        } else {
            $tahunPelajaran = $kelas->getTahunPelajaran();
        }

        // Cek duplikasi nama kelas pada entitas lain di tahun ajaran yang sama
        $existing = $this->kelasRepository->findOneBy([
            'namaKelas' => $dto->namaKelas,
            'tahunPelajaran' => $tahunPelajaran,
        ]);

        if ($existing !== null && $existing->getId() !== $kelas->getId()) {
            throw new InvalidArgumentException(sprintf(
                'Kelas dengan nama "%s" sudah terdaftar untuk %s.',
                $dto->namaKelas,
                $tahunPelajaran ? $tahunPelajaran->getNamaLengkap() : 'tahun pelajaran ini'
            ));
        }

        $waliKelas = null;
        if ($dto->waliKelasId !== null) {
            $waliKelas = $this->userRepository->find($dto->waliKelasId);
        }

        $kelas->setNamaKelas($dto->namaKelas);
        $kelas->setTingkat($dto->tingkat);
        $kelas->setJurusan($dto->jurusan);
        $kelas->setTahunPelajaran($tahunPelajaran);
        $kelas->setWaliKelas($waliKelas);
        $kelas->setKapasitas($dto->kapasitas);
        $kelas->setKeterangan($dto->keterangan);
        $kelas->setIsActive($dto->isActive);

        $this->kelasRepository->save($kelas, true);

        return KelasResponseDTO::fromEntity($kelas);
    }

    public function deleteKelas(int $id): void
    {
        $kelas = $this->kelasRepository->find($id);
        if ($kelas !== null) {
            $this->kelasRepository->remove($kelas, true);
        }
    }

    public function importKelas(ImportKelasBatchDTO $dto): ImportKelasResultDTO
    {
        if (empty($dto->items)) {
            return new ImportKelasResultDTO(0, 0, 0, 0, ['Tidak ada data kelas yang valid untuk diimpor.']);
        }

        $tahunPelajaran = null;
        if ($dto->tahunPelajaranId !== null) {
            $tahunPelajaran = $this->tahunPelajaranRepository->find($dto->tahunPelajaranId);
        } else {
            $tahunPelajaran = $this->tahunPelajaranRepository->findActive();
        }

        // Siapkan lookup map untuk guru pengampu / wali kelas
        $guruList = $this->userRepository->searchUsers();
        $guruMapById = [];
        $guruMapByNip = [];
        $guruMapByName = [];
        foreach ($guruList as $guru) {
            $guruMapById[(string) $guru->getId()] = $guru;
            if ($guru->getIdentifierNumber()) {
                $guruMapByNip[strtolower(trim($guru->getIdentifierNumber()))] = $guru;
            }
            $guruMapByName[strtolower(trim($guru->getName()))] = $guru;
        }

        $totalProcessed = 0;
        $totalCreated = 0;
        $totalUpdated = 0;
        $totalSkipped = 0;
        $errors = [];

        foreach ($dto->items as $item) {
            $namaKelas = trim($item->namaKelas);
            if ($namaKelas === '') {
                $totalSkipped++;
                continue;
            }

            $totalProcessed++;

            // Cari wali kelas jika diisi
            $waliKelas = null;
            if ($item->waliKelas !== null && $item->waliKelas !== '') {
                $rawWali = trim($item->waliKelas);
                if (isset($guruMapById[$rawWali])) {
                    $waliKelas = $guruMapById[$rawWali];
                } elseif (isset($guruMapByNip[strtolower($rawWali)])) {
                    $waliKelas = $guruMapByNip[strtolower($rawWali)];
                } elseif (isset($guruMapByName[strtolower($rawWali)])) {
                    $waliKelas = $guruMapByName[strtolower($rawWali)];
                } else {
                    // Coba partial match nama guru
                    foreach ($guruMapByName as $guruName => $g) {
                        if (str_contains($guruName, strtolower($rawWali)) || str_contains(strtolower($rawWali), $guruName)) {
                            $waliKelas = $g;
                            break;
                        }
                    }
                }
            }

            // Cek kelas yang sudah ada dengan nama & tahun pelajaran yang sama
            $existing = $this->kelasRepository->findOneBy([
                'namaKelas' => $namaKelas,
                'tahunPelajaran' => $tahunPelajaran,
            ]);

            if ($existing !== null) {
                if ($dto->onDuplicate === 'update') {
                    $existing->setTingkat($item->tingkat);
                    if ($item->jurusan !== null) {
                        $existing->setJurusan($item->jurusan);
                    }
                    if ($waliKelas !== null) {
                        $existing->setWaliKelas($waliKelas);
                    }
                    $existing->setKapasitas($item->kapasitas);
                    if ($item->keterangan !== null) {
                        $existing->setKeterangan($item->keterangan);
                    }
                    $existing->setIsActive($item->isActive);
                    $this->kelasRepository->save($existing, false);
                    $totalUpdated++;
                } else {
                    $totalSkipped++;
                }
            } else {
                $newKelas = new Kelas();
                $newKelas->setNamaKelas($namaKelas);
                $newKelas->setTingkat($item->tingkat);
                $newKelas->setJurusan($item->jurusan);
                $newKelas->setTahunPelajaran($tahunPelajaran);
                $newKelas->setWaliKelas($waliKelas);
                $newKelas->setKapasitas($item->kapasitas);
                $newKelas->setKeterangan($item->keterangan);
                $newKelas->setIsActive($item->isActive);

                $this->kelasRepository->save($newKelas, false);
                $totalCreated++;
            }
        }

        $this->entityManager->flush();

        return new ImportKelasResultDTO(
            totalProcessed: $totalProcessed,
            totalCreated: $totalCreated,
            totalUpdated: $totalUpdated,
            totalSkipped: $totalSkipped,
            errors: $errors,
        );
    }

    /**
     * Menyediakan master data opsi untuk dropdown form modal
     * @return array<string, mixed>
     */
    public function getFormData(): array
    {
        $tahunPelajaranList = $this->tahunPelajaranRepository->findBy([], ['tahun' => 'DESC', 'semester' => 'ASC']);
        $activeTahun = $this->tahunPelajaranRepository->findActive();

        // Ambil data staf pengajar / guru
        $guruList = $this->userRepository->searchUsers();
        // Filter user yang memiliki peran guru/staf
        $guruOptions = array_values(array_map(fn($g) => [
            'id' => (int) $g->getId(),
            'name' => $g->getName(),
            'nip' => $g->getIdentifierNumber(),
        ], $guruList));

        return [
            'tahunPelajaranOptions' => array_map(fn($tp) => [
                'id' => (int) $tp->getId(),
                'nama' => $tp->getNamaLengkap(),
                'isActive' => $tp->isActive(),
            ], $tahunPelajaranList),
            'activeTahunPelajaranId' => $activeTahun !== null ? (int) $activeTahun->getId() : null,
            'guruOptions' => $guruOptions,
        ];
    }
}

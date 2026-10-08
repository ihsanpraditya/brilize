<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Kelas\CreateKelasDTO;
use App\DTO\Kelas\KelasResponseDTO;
use App\DTO\Kelas\UpdateKelasDTO;
use App\Entity\Kelas;
use App\Enum\UserRole;
use App\Repository\KelasRepository;
use App\Repository\TahunPelajaranRepository;
use App\Repository\UserRepository;
use InvalidArgumentException;

final readonly class KelasService
{
    public function __construct(
        private KelasRepository $kelasRepository,
        private TahunPelajaranRepository $tahunPelajaranRepository,
        private UserRepository $userRepository,
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

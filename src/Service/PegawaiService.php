<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Pegawai\CreatePegawaiDTO;
use App\DTO\Pegawai\PegawaiResponseDTO;
use App\DTO\Pegawai\UpdatePegawaiDTO;
use App\Entity\Guru;
use App\Entity\Pegawai;
use App\Enum\JenisKepegawaian;
use App\Enum\JenisKelamin;
use App\Enum\KategoriPegawai;
use App\Enum\StatusKepegawaian;
use App\Repository\GuruRepository;
use App\Repository\PegawaiRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use InvalidArgumentException;

final readonly class PegawaiService
{
    public function __construct(
        private PegawaiRepository $pegawaiRepository,
        private GuruRepository $guruRepository,
        private UserRepository $userRepository,
    ) {}

    /**
     * @return list<PegawaiResponseDTO>
     */
    public function getAllPegawai(
        ?string $query = null,
        ?KategoriPegawai $kategori = null,
        ?string $jenis = null,
        ?StatusKepegawaian $status = null,
        ?bool $isActive = null
    ): array {
        $jenisEnum = null;
        if ($jenis !== null && $jenis !== '') {
            $jenisEnum = \App\Enum\JenisPegawai::tryFrom($jenis);
        }

        $list = $this->pegawaiRepository->searchPegawai($query, $kategori, $jenisEnum, $status, $isActive);

        return array_map(fn(Pegawai $p) => PegawaiResponseDTO::fromEntity($p), $list);
    }

    public function getPegawaiById(int $id): ?PegawaiResponseDTO
    {
        $entity = $this->pegawaiRepository->find($id);

        return $entity !== null ? PegawaiResponseDTO::fromEntity($entity) : null;
    }

    public function createPegawai(CreatePegawaiDTO $dto): PegawaiResponseDTO
    {
        $errors = $dto->validate();
        if (!empty($errors)) {
            throw new InvalidArgumentException(reset($errors));
        }

        // Cek duplikasi NIP / NUPTK / NIK
        if ($dto->nip !== null && $this->pegawaiRepository->findOneBy(['nip' => $dto->nip])) {
            throw new InvalidArgumentException('Nomor NIP sudah terdaftar pada sistem.');
        }

        if ($dto->nuptk !== null && $this->pegawaiRepository->findOneBy(['nuptk' => $dto->nuptk])) {
            throw new InvalidArgumentException('Nomor NUPTK sudah terdaftar pada sistem.');
        }

        if ($dto->nik !== null && $this->pegawaiRepository->findOneBy(['nik' => $dto->nik])) {
            throw new InvalidArgumentException('Nomor NIK KTP sudah terdaftar pada sistem.');
        }

        $pegawai = new Pegawai();
        $pegawai->setNamaLengkap($dto->namaLengkap);
        $pegawai->setGelarDepan($dto->gelarDepan);
        $pegawai->setGelarBelakang($dto->gelarBelakang);
        $pegawai->setNip($dto->nip);
        $pegawai->setNuptk($dto->nuptk);
        $pegawai->setNik($dto->nik);
        $pegawai->setJenisKelamin($dto->jenisKelamin);
        $pegawai->setTempatLahir($dto->tempatLahir);
        $pegawai->setTanggalLahir($dto->tanggalLahir ? new DateTimeImmutable($dto->tanggalLahir) : null);
        $pegawai->setAgama($dto->agama);
        $pegawai->setAlamat($dto->alamat);
        $pegawai->setNomorHp($dto->nomorHp);
        $pegawai->setEmail($dto->email);
        $pegawai->setKategoriPegawai($dto->kategoriPegawai);

        $jenisPegawai = \App\Enum\JenisPegawai::tryFrom($dto->jenisPegawai) ?? \App\Enum\JenisPegawai::GURU;
        $pegawai->setJenisPegawai($jenisPegawai);
        $pegawai->setStatusKepegawaian($dto->statusKepegawaian);
        $pegawai->setJabatan($dto->jabatan);
        $pegawai->setPendidikanTerakhir($dto->pendidikanTerakhir);
        $pegawai->setJurusanPendidikan($dto->jurusanPendidikan);
        $pegawai->setTanggalMasuk($dto->tanggalMasuk ? new DateTimeImmutable($dto->tanggalMasuk) : null);
        $pegawai->setIsActive($dto->isActive);

        // Jika merupakan Tenaga Pendidik (Guru), inisialisasi entitas spesialisasi Guru
        if ($dto->kategoriPegawai === KategoriPegawai::PENDIDIK || $jenisPegawai === \App\Enum\JenisPegawai::GURU) {
            $guru = new Guru();
            $guru->setPegawai($pegawai);
            $guru->setBidangStudiUtama($dto->bidangStudiUtama);
            $guru->setIsSertifikasi($dto->isSertifikasi);
            $guru->setNoSertifikasi($dto->noSertifikasi);
            $guru->setTugasTambahan($dto->tugasTambahan);
            $guru->setTmtPendidik($dto->tmtPendidik ? new DateTimeImmutable($dto->tmtPendidik) : null);

            $pegawai->setGuru($guru);
        }

        $this->pegawaiRepository->save($pegawai, true);

        return PegawaiResponseDTO::fromEntity($pegawai);
    }

    public function updatePegawai(int $id, UpdatePegawaiDTO $dto): PegawaiResponseDTO
    {
        $pegawai = $this->pegawaiRepository->find($id);
        if ($pegawai === null) {
            throw new InvalidArgumentException('Data Pegawai / Guru tidak ditemukan.');
        }

        $errors = $dto->validate();
        if (!empty($errors)) {
            throw new InvalidArgumentException(reset($errors));
        }

        // Cek duplikasi identitas
        if ($dto->nip !== null) {
            $existing = $this->pegawaiRepository->findOneBy(['nip' => $dto->nip]);
            if ($existing && $existing->getId() !== $pegawai->getId()) {
                throw new InvalidArgumentException('Nomor NIP sudah digunakan oleh staf lain.');
            }
        }

        if ($dto->nuptk !== null) {
            $existing = $this->pegawaiRepository->findOneBy(['nuptk' => $dto->nuptk]);
            if ($existing && $existing->getId() !== $pegawai->getId()) {
                throw new InvalidArgumentException('Nomor NUPTK sudah digunakan oleh staf lain.');
            }
        }

        $pegawai->setNamaLengkap($dto->namaLengkap);
        $pegawai->setGelarDepan($dto->gelarDepan);
        $pegawai->setGelarBelakang($dto->gelarBelakang);
        $pegawai->setNip($dto->nip);
        $pegawai->setNuptk($dto->nuptk);
        $pegawai->setNik($dto->nik);
        $pegawai->setJenisKelamin($dto->jenisKelamin);
        $pegawai->setTempatLahir($dto->tempatLahir);
        $pegawai->setTanggalLahir($dto->tanggalLahir ? new DateTimeImmutable($dto->tanggalLahir) : null);
        $pegawai->setAgama($dto->agama);
        $pegawai->setAlamat($dto->alamat);
        $pegawai->setNomorHp($dto->nomorHp);
        $pegawai->setEmail($dto->email);
        $pegawai->setKategoriPegawai($dto->kategoriPegawai);

        $jenisPegawai = \App\Enum\JenisPegawai::tryFrom($dto->jenisPegawai) ?? \App\Enum\JenisPegawai::GURU;
        $pegawai->setJenisPegawai($jenisPegawai);
        $pegawai->setStatusKepegawaian($dto->statusKepegawaian);
        $pegawai->setJabatan($dto->jabatan);
        $pegawai->setPendidikanTerakhir($dto->pendidikanTerakhir);
        $pegawai->setJurusanPendidikan($dto->jurusanPendidikan);
        $pegawai->setTanggalMasuk($dto->tanggalMasuk ? new DateTimeImmutable($dto->tanggalMasuk) : null);
        $pegawai->setIsActive($dto->isActive);

        // Update relasi Guru
        if ($dto->kategoriPegawai === KategoriPegawai::PENDIDIK || $jenisPegawai === \App\Enum\JenisPegawai::GURU) {
            $guru = $pegawai->getGuru() ?? new Guru();
            $guru->setPegawai($pegawai);
            $guru->setBidangStudiUtama($dto->bidangStudiUtama);
            $guru->setIsSertifikasi($dto->isSertifikasi);
            $guru->setNoSertifikasi($dto->noSertifikasi);
            $guru->setTugasTambahan($dto->tugasTambahan);
            $guru->setTmtPendidik($dto->tmtPendidik ? new DateTimeImmutable($dto->tmtPendidik) : null);

            $pegawai->setGuru($guru);
        }

        $this->pegawaiRepository->save($pegawai, true);

        return PegawaiResponseDTO::fromEntity($pegawai);
    }

    public function deletePegawai(int $id): void
    {
        $pegawai = $this->pegawaiRepository->find($id);
        if ($pegawai !== null) {
            $this->pegawaiRepository->remove($pegawai, true);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormData(): array
    {
        return [
            'statistik' => $this->pegawaiRepository->getStatistikPegawai(),
            'kategoriOptions' => array_map(fn($k) => [
                'value' => $k->value,
                'label' => $k->label(),
            ], KategoriPegawai::cases()),
            'statusOptions' => array_map(fn($s) => [
                'value' => $s->value,
                'label' => $s->label(),
                'badge' => $s->badgeClass(),
            ], StatusKepegawaian::cases()),
            'jenisOptions' => array_map(fn($j) => [
                'value' => $j->value,
                'label' => $j->label(),
            ], \App\Enum\JenisPegawai::cases()),
            'jenisKelaminOptions' => array_map(fn($jk) => [
                'value' => $jk->value,
                'label' => $jk->label(),
            ], JenisKelamin::cases()),
        ];
    }
}

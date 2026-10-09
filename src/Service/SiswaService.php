<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Siswa\CreateSiswaDTO;
use App\DTO\Siswa\SiswaResponseDTO;
use App\DTO\Siswa\UpdateSiswaDTO;
use App\Entity\Siswa;
use App\Entity\User;
use App\Enum\JenisKelamin;
use App\Enum\StatusSiswa;
use App\Enum\UserRole;
use App\Enum\UserStatus;
use App\Repository\KelasRepository;
use App\Repository\SiswaRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class SiswaService
{
    public function __construct(
        private SiswaRepository $siswaRepository,
        private KelasRepository $kelasRepository,
        private UserRepository $userRepository,
        private EntityManagerInterface $em,
        private ?UserPasswordHasherInterface $passwordHasher = null,
    ) {}

    /**
     * @return list<SiswaResponseDTO>
     */
    public function getAllSiswa(
        ?string $query = null,
        ?int $kelasId = null,
        ?StatusSiswa $status = null,
        ?JenisKelamin $jenisKelamin = null,
        ?bool $isActive = null
    ): array {
        $list = $this->siswaRepository->searchSiswa($query, $kelasId, $status, $jenisKelamin, $isActive);

        return array_map(fn(Siswa $s) => SiswaResponseDTO::fromEntity($s), $list);
    }

    public function getSiswaById(int $id): ?SiswaResponseDTO
    {
        $entity = $this->siswaRepository->find($id);

        return $entity !== null ? SiswaResponseDTO::fromEntity($entity) : null;
    }

    public function getSiswaEntityById(int $id): ?Siswa
    {
        return $this->siswaRepository->find($id);
    }

    public function createSiswa(CreateSiswaDTO $dto): SiswaResponseDTO
    {
        $errors = $dto->validate();
        if (!empty($errors)) {
            throw new InvalidArgumentException(reset($errors));
        }

        // Cek duplikasi NIS
        if ($this->siswaRepository->findOneBy(['nis' => $dto->nis])) {
            throw new InvalidArgumentException(sprintf('Siswa dengan NIS "%s" sudah terdaftar.', $dto->nis));
        }

        // Cek duplikasi NISN jika diisi
        if ($dto->nisn !== null && $this->siswaRepository->findOneBy(['nisn' => $dto->nisn])) {
            throw new InvalidArgumentException(sprintf('Siswa dengan NISN "%s" sudah terdaftar.', $dto->nisn));
        }

        // Cek duplikasi NIK jika diisi
        if ($dto->nik !== null && $this->siswaRepository->findOneBy(['nik' => $dto->nik])) {
            throw new InvalidArgumentException(sprintf('Siswa dengan NIK "%s" sudah terdaftar.', $dto->nik));
        }

        $siswa = new Siswa();
        $siswa->setNis($dto->nis);
        $siswa->setNisn($dto->nisn);
        $siswa->setNik($dto->nik);
        $siswa->setNamaLengkap($dto->namaLengkap);
        $siswa->setNamaPanggilan($dto->namaPanggilan);
        $siswa->setNamaArab($dto->namaArab);
        $siswa->setJenisKelamin($dto->jenisKelamin);
        $siswa->setTempatLahir($dto->tempatLahir);
        $siswa->setTanggalLahir($dto->tanggalLahir ? new DateTimeImmutable($dto->tanggalLahir) : null);
        $siswa->setAgama($dto->agama);
        $siswa->setAlamat($dto->alamat);
        $siswa->setRt($dto->rt);
        $siswa->setRw($dto->rw);
        $siswa->setKelurahan($dto->kelurahan);
        $siswa->setKecamatan($dto->kecamatan);
        $siswa->setKabupatenKota($dto->kabupatenKota);
        $siswa->setProvinsi($dto->provinsi);
        $siswa->setKodePos($dto->kodePos);
        $siswa->setNomorHp($dto->nomorHp);
        $siswa->setEmail($dto->email);
        $siswa->setStatus($dto->status);
        $siswa->setIsActive($dto->isActive);

        // Relasi Kelas
        if ($dto->kelasId !== null) {
            $kelas = $this->kelasRepository->find($dto->kelasId);
            $siswa->setKelas($kelas);
        }

        // Data Orang Tua / Wali
        $siswa->setNamaAyah($dto->namaAyah);
        $siswa->setPekerjaanAyah($dto->pekerjaanAyah);
        $siswa->setPenghasilanAyah($dto->penghasilanAyah);
        $siswa->setNoHpAyah($dto->noHpAyah);
        $siswa->setNamaIbu($dto->namaIbu);
        $siswa->setPekerjaanIbu($dto->pekerjaanIbu);
        $siswa->setPenghasilanIbu($dto->penghasilanIbu);
        $siswa->setNoHpIbu($dto->noHpIbu);
        $siswa->setNamaWali($dto->namaWali);
        $siswa->setHubunganWali($dto->hubunganWali);
        $siswa->setPekerjaanWali($dto->pekerjaanWali);
        $siswa->setNoHpWali($dto->noHpWali);
        $siswa->setAlamatOrangTua($dto->alamatOrangTua);

        // Data Tambahan
        $siswa->setAnakKe($dto->anakKe);
        $siswa->setJumlahSaudara($dto->jumlahSaudara);
        $siswa->setTanggalMasuk($dto->tanggalMasuk ? new DateTimeImmutable($dto->tanggalMasuk) : null);
        $siswa->setAsalSekolah($dto->asalSekolah);
        $siswa->setFotoUrl($dto->fotoUrl);
        $siswa->setCatatan($dto->catatan);

        // Opsi Buat Akun User Siswa
        if ($dto->createUserAccount) {
            $username = strtolower($dto->nis);
            $user = new User();
            $user->setName($dto->namaLengkap);
            $user->setUsername($username);
            $user->setIdentifierNumber($dto->nisn ?? $dto->nis);
            $user->setEmail($dto->email);
            $user->setPhone($dto->nomorHp);
            $user->setRoles([UserRole::SISWA->value]);
            $user->setStatus(UserStatus::ACTIVE);

            // Default password: NIS
            $plainPassword = $dto->nis;
            if ($this->passwordHasher !== null) {
                $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
            } else {
                $user->setPassword(password_hash($plainPassword, PASSWORD_BCRYPT));
            }

            $this->em->persist($user);
            $siswa->setUser($user);
        }

        $this->siswaRepository->save($siswa, true);

        return SiswaResponseDTO::fromEntity($siswa);
    }

    public function updateSiswa(int $id, UpdateSiswaDTO $dto): SiswaResponseDTO
    {
        $siswa = $this->siswaRepository->find($id);
        if ($siswa === null) {
            throw new InvalidArgumentException('Data siswa tidak ditemukan.');
        }

        $errors = $dto->validate();
        if (!empty($errors)) {
            throw new InvalidArgumentException(reset($errors));
        }

        // Cek duplikasi NIS
        $existingNis = $this->siswaRepository->findOneBy(['nis' => $dto->nis]);
        if ($existingNis !== null && $existingNis->getId() !== $siswa->getId()) {
            throw new InvalidArgumentException(sprintf('NIS "%s" sudah digunakan oleh siswa lain.', $dto->nis));
        }

        // Cek duplikasi NISN
        if ($dto->nisn !== null) {
            $existingNisn = $this->siswaRepository->findOneBy(['nisn' => $dto->nisn]);
            if ($existingNisn !== null && $existingNisn->getId() !== $siswa->getId()) {
                throw new InvalidArgumentException(sprintf('NISN "%s" sudah digunakan oleh siswa lain.', $dto->nisn));
            }
        }

        // Cek duplikasi NIK
        if ($dto->nik !== null) {
            $existingNik = $this->siswaRepository->findOneBy(['nik' => $dto->nik]);
            if ($existingNik !== null && $existingNik->getId() !== $siswa->getId()) {
                throw new InvalidArgumentException(sprintf('NIK "%s" sudah digunakan oleh siswa lain.', $dto->nik));
            }
        }

        $siswa->setNis($dto->nis);
        $siswa->setNisn($dto->nisn);
        $siswa->setNik($dto->nik);
        $siswa->setNamaLengkap($dto->namaLengkap);
        $siswa->setNamaPanggilan($dto->namaPanggilan);
        $siswa->setNamaArab($dto->namaArab);
        $siswa->setJenisKelamin($dto->jenisKelamin);
        $siswa->setTempatLahir($dto->tempatLahir);
        $siswa->setTanggalLahir($dto->tanggalLahir ? new DateTimeImmutable($dto->tanggalLahir) : null);
        $siswa->setAgama($dto->agama);
        $siswa->setAlamat($dto->alamat);
        $siswa->setRt($dto->rt);
        $siswa->setRw($dto->rw);
        $siswa->setKelurahan($dto->kelurahan);
        $siswa->setKecamatan($dto->kecamatan);
        $siswa->setKabupatenKota($dto->kabupatenKota);
        $siswa->setProvinsi($dto->provinsi);
        $siswa->setKodePos($dto->kodePos);
        $siswa->setNomorHp($dto->nomorHp);
        $siswa->setEmail($dto->email);
        $siswa->setStatus($dto->status);
        $siswa->setIsActive($dto->isActive);

        // Relasi Kelas
        if ($dto->kelasId !== null) {
            $kelas = $this->kelasRepository->find($dto->kelasId);
            $siswa->setKelas($kelas);
        } else {
            $siswa->setKelas(null);
        }

        // Data Orang Tua / Wali
        $siswa->setNamaAyah($dto->namaAyah);
        $siswa->setPekerjaanAyah($dto->pekerjaanAyah);
        $siswa->setPenghasilanAyah($dto->penghasilanAyah);
        $siswa->setNoHpAyah($dto->noHpAyah);
        $siswa->setNamaIbu($dto->namaIbu);
        $siswa->setPekerjaanIbu($dto->pekerjaanIbu);
        $siswa->setPenghasilanIbu($dto->penghasilanIbu);
        $siswa->setNoHpIbu($dto->noHpIbu);
        $siswa->setNamaWali($dto->namaWali);
        $siswa->setHubunganWali($dto->hubunganWali);
        $siswa->setPekerjaanWali($dto->pekerjaanWali);
        $siswa->setNoHpWali($dto->noHpWali);
        $siswa->setAlamatOrangTua($dto->alamatOrangTua);

        // Data Tambahan
        $siswa->setAnakKe($dto->anakKe);
        $siswa->setJumlahSaudara($dto->jumlahSaudara);
        $siswa->setTanggalMasuk($dto->tanggalMasuk ? new DateTimeImmutable($dto->tanggalMasuk) : null);
        $siswa->setAsalSekolah($dto->asalSekolah);
        $siswa->setFotoUrl($dto->fotoUrl);
        $siswa->setCatatan($dto->catatan);

        $this->siswaRepository->save($siswa, true);

        return SiswaResponseDTO::fromEntity($siswa);
    }

    public function deleteSiswa(int $id): void
    {
        $siswa = $this->siswaRepository->find($id);
        if ($siswa === null) {
            throw new InvalidArgumentException('Data siswa tidak ditemukan.');
        }

        $this->siswaRepository->remove($siswa, true);
    }

    public function toggleStatus(int $id): SiswaResponseDTO
    {
        $siswa = $this->siswaRepository->find($id);
        if ($siswa === null) {
            throw new InvalidArgumentException('Data siswa tidak ditemukan.');
        }

        $siswa->setIsActive(!$siswa->isActive());
        $this->siswaRepository->save($siswa, true);

        return SiswaResponseDTO::fromEntity($siswa);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormData(): array
    {
        $kelasList = $this->kelasRepository->findBy(['isActive' => true], ['namaKelas' => 'ASC']);

        return [
            'kelas' => array_map(fn($k) => [
                'id' => (int) $k->getId(),
                'namaKelas' => $k->getNamaKelas(),
                'tingkat' => $k->getTingkat(),
                'jurusan' => $k->getJurusan(),
            ], $kelasList),
            'jenisKelamin' => array_map(fn(JenisKelamin $jk) => [
                'value' => $jk->value,
                'label' => $jk->label(),
            ], JenisKelamin::cases()),
            'statusSiswa' => array_map(fn(StatusSiswa $s) => [
                'value' => $s->value,
                'label' => $s->label(),
                'badge' => $s->badgeClass(),
            ], StatusSiswa::cases()),
            'agama' => [
                'Islam',
                'Kristen Protestan',
                'Katolik',
                'Hindu',
                'Buddha',
                'Konghucu',
            ],
        ];
    }

    /**
     * @return array<string, int>
     */
    public function getStatistics(): array
    {
        $counts = $this->siswaRepository->countByStatus();
        $total = array_sum($counts);

        return [
            'total' => $total,
            'aktif' => $counts[StatusSiswa::AKTIF->value] ?? 0,
            'lulus' => $counts[StatusSiswa::LULUS->value] ?? 0,
            'mutasi' => $counts[StatusSiswa::MUTASI->value] ?? 0,
            'keluar' => $counts[StatusSiswa::KELUAR->value] ?? 0,
            'nonaktif' => $counts[StatusSiswa::NONAKTIF->value] ?? 0,
        ];
    }
}

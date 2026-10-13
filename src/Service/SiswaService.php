<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Siswa\CreateSiswaDTO;
use App\DTO\Siswa\ImportSiswaBatchDTO;
use App\DTO\Siswa\ImportSiswaResultDTO;
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
final readonly class SiswaService
{
    public function __construct(
        private SiswaRepository $siswaRepository,
        private KelasRepository $kelasRepository,
        private UserRepository $userRepository,
        private EntityManagerInterface $em,
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

            // Default password login: NIS siswa
            $plainPassword = $dto->nis;
            $user->setPassword(password_hash($plainPassword, PASSWORD_BCRYPT));

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

    public function importSiswa(ImportSiswaBatchDTO $dto): ImportSiswaResultDTO
    {
        if (empty($dto->items)) {
            return new ImportSiswaResultDTO(0, 0, 0, 0, ['Tidak ada data siswa yang valid untuk diimpor.']);
        }

        // Siapkan lookup map untuk Kelas
        $allKelas = $this->kelasRepository->findAll();
        $kelasMapById = [];
        $kelasMapByName = [];
        $kelasMapByNormalized = [];

        foreach ($allKelas as $k) {
            $id = (int) $k->getId();
            $kelasMapById[$id] = $k;
            $nameLower = strtolower(trim($k->getNamaKelas()));
            $kelasMapByName[$nameLower] = $k;
            $normalized = preg_replace('/[^a-z0-9]/', '', $nameLower);
            if ($normalized) {
                $kelasMapByNormalized[$normalized] = $k;
            }
        }

        $defaultKelas = null;
        if ($dto->defaultKelasId !== null && isset($kelasMapById[$dto->defaultKelasId])) {
            $defaultKelas = $kelasMapById[$dto->defaultKelasId];
        }

        $totalProcessed = 0;
        $totalCreated = 0;
        $totalUpdated = 0;
        $totalSkipped = 0;
        $errors = [];

        foreach ($dto->items as $item) {
            $nis = trim($item->nis);
            $namaLengkap = trim($item->namaLengkap);

            if ($nis === '' || $namaLengkap === '') {
                $totalSkipped++;
                continue;
            }

            $totalProcessed++;

            // Resolusi kelas
            $kelas = null;
            if ($item->kelas !== null && $item->kelas !== '') {
                $rawKelas = trim($item->kelas);
                if (is_numeric($rawKelas) && isset($kelasMapById[(int) $rawKelas])) {
                    $kelas = $kelasMapById[(int) $rawKelas];
                } else {
                    $kLower = strtolower($rawKelas);
                    $kNorm = preg_replace('/[^a-z0-9]/', '', $kLower);
                    if (isset($kelasMapByName[$kLower])) {
                        $kelas = $kelasMapByName[$kLower];
                    } elseif ($kNorm && isset($kelasMapByNormalized[$kNorm])) {
                        $kelas = $kelasMapByNormalized[$kNorm];
                    }
                }
            }

            if ($kelas === null) {
                $kelas = $defaultKelas;
            }

            $existing = $this->siswaRepository->findOneBy(['nis' => $nis]);

            $tanggalLahir = $this->parseDateSafe($item->tanggalLahir);
            $tanggalMasuk = $this->parseDateSafe($item->tanggalMasuk);

            if ($existing !== null) {
                if ($dto->onDuplicate === 'update') {
                    $existing->setNamaLengkap($namaLengkap);
                    $existing->setJenisKelamin($item->jenisKelamin);
                    if ($item->nisn !== null) $existing->setNisn($item->nisn);
                    if ($item->nik !== null) $existing->setNik($item->nik);
                    if ($item->namaPanggilan !== null) $existing->setNamaPanggilan($item->namaPanggilan);
                    if ($item->namaArab !== null) $existing->setNamaArab($item->namaArab);
                    if ($item->tempatLahir !== null) $existing->setTempatLahir($item->tempatLahir);
                    if ($tanggalLahir !== null) $existing->setTanggalLahir($tanggalLahir);
                    if ($item->agama !== null) $existing->setAgama($item->agama);
                    if ($item->alamat !== null) $existing->setAlamat($item->alamat);
                    if ($item->rt !== null) $existing->setRt($item->rt);
                    if ($item->rw !== null) $existing->setRw($item->rw);
                    if ($item->kelurahan !== null) $existing->setKelurahan($item->kelurahan);
                    if ($item->kecamatan !== null) $existing->setKecamatan($item->kecamatan);
                    if ($item->kabupatenKota !== null) $existing->setKabupatenKota($item->kabupatenKota);
                    if ($item->provinsi !== null) $existing->setProvinsi($item->provinsi);
                    if ($item->kodePos !== null) $existing->setKodePos($item->kodePos);
                    if ($item->nomorHp !== null) $existing->setNomorHp($item->nomorHp);
                    if ($item->email !== null) $existing->setEmail($item->email);
                    if ($kelas !== null) $existing->setKelas($kelas);
                    $existing->setStatus($item->status);
                    if ($item->namaAyah !== null) $existing->setNamaAyah($item->namaAyah);
                    if ($item->pekerjaanAyah !== null) $existing->setPekerjaanAyah($item->pekerjaanAyah);
                    if ($item->penghasilanAyah !== null) $existing->setPenghasilanAyah($item->penghasilanAyah);
                    if ($item->noHpAyah !== null) $existing->setNoHpAyah($item->noHpAyah);
                    if ($item->namaIbu !== null) $existing->setNamaIbu($item->namaIbu);
                    if ($item->pekerjaanIbu !== null) $existing->setPekerjaanIbu($item->pekerjaanIbu);
                    if ($item->penghasilanIbu !== null) $existing->setPenghasilanIbu($item->penghasilanIbu);
                    if ($item->noHpIbu !== null) $existing->setNoHpIbu($item->noHpIbu);
                    if ($item->namaWali !== null) $existing->setNamaWali($item->namaWali);
                    if ($item->hubunganWali !== null) $existing->setHubunganWali($item->hubunganWali);
                    if ($item->pekerjaanWali !== null) $existing->setPekerjaanWali($item->pekerjaanWali);
                    if ($item->noHpWali !== null) $existing->setNoHpWali($item->noHpWali);
                    if ($item->alamatOrangTua !== null) $existing->setAlamatOrangTua($item->alamatOrangTua);
                    if ($item->anakKe !== null) $existing->setAnakKe($item->anakKe);
                    if ($item->jumlahSaudara !== null) $existing->setJumlahSaudara($item->jumlahSaudara);
                    if ($tanggalMasuk !== null) $existing->setTanggalMasuk($tanggalMasuk);
                    if ($item->asalSekolah !== null) $existing->setAsalSekolah($item->asalSekolah);
                    if ($item->catatan !== null) $existing->setCatatan($item->catatan);
                    $existing->setIsActive($item->isActive);

                    $this->siswaRepository->save($existing, false);
                    $totalUpdated++;
                } else {
                    $totalSkipped++;
                }
            } else {
                $siswa = new Siswa();
                $siswa->setNis($nis);
                $siswa->setNamaLengkap($namaLengkap);
                $siswa->setJenisKelamin($item->jenisKelamin);
                $siswa->setNisn($item->nisn);
                $siswa->setNik($item->nik);
                $siswa->setNamaPanggilan($item->namaPanggilan);
                $siswa->setNamaArab($item->namaArab);
                $siswa->setTempatLahir($item->tempatLahir);
                $siswa->setTanggalLahir($tanggalLahir);
                $siswa->setAgama($item->agama ?? 'Islam');
                $siswa->setAlamat($item->alamat);
                $siswa->setRt($item->rt);
                $siswa->setRw($item->rw);
                $siswa->setKelurahan($item->kelurahan);
                $siswa->setKecamatan($item->kecamatan);
                $siswa->setKabupatenKota($item->kabupatenKota);
                $siswa->setProvinsi($item->provinsi);
                $siswa->setKodePos($item->kodePos);
                $siswa->setNomorHp($item->nomorHp);
                $siswa->setEmail($item->email);
                $siswa->setKelas($kelas);
                $siswa->setStatus($item->status);
                $siswa->setNamaAyah($item->namaAyah);
                $siswa->setPekerjaanAyah($item->pekerjaanAyah);
                $siswa->setPenghasilanAyah($item->penghasilanAyah);
                $siswa->setNoHpAyah($item->noHpAyah);
                $siswa->setNamaIbu($item->namaIbu);
                $siswa->setPekerjaanIbu($item->pekerjaanIbu);
                $siswa->setPenghasilanIbu($item->penghasilanIbu);
                $siswa->setNoHpIbu($item->noHpIbu);
                $siswa->setNamaWali($item->namaWali);
                $siswa->setHubunganWali($item->hubunganWali);
                $siswa->setPekerjaanWali($item->pekerjaanWali);
                $siswa->setNoHpWali($item->noHpWali);
                $siswa->setAlamatOrangTua($item->alamatOrangTua);
                $siswa->setAnakKe($item->anakKe);
                $siswa->setJumlahSaudara($item->jumlahSaudara);
                $siswa->setTanggalMasuk($tanggalMasuk);
                $siswa->setAsalSekolah($item->asalSekolah);
                $siswa->setCatatan($item->catatan);
                $siswa->setIsActive($item->isActive);

                // Opsi Buat Akun User Siswa
                if ($dto->createUserAccount) {
                    $username = strtolower($nis);
                    $existingUser = $this->userRepository->findOneBy(['username' => $username]);
                    if ($existingUser === null) {
                        $user = new User();
                        $user->setName($namaLengkap);
                        $user->setUsername($username);
                        $user->setIdentifierNumber($item->nisn ?? $nis);
                        $user->setEmail($item->email);
                        $user->setPhone($item->nomorHp);
                        $user->setRoles([UserRole::SISWA->value]);
                        $user->setStatus(UserStatus::ACTIVE);

                        // Default password: NIS siswa
                        $user->setPassword(password_hash($nis, PASSWORD_BCRYPT));
                        $this->em->persist($user);
                        $siswa->setUser($user);
                    } else {
                        $siswa->setUser($existingUser);
                    }
                }

                $this->siswaRepository->save($siswa, false);
                $totalCreated++;
            }
        }

        $this->em->flush();

        return new ImportSiswaResultDTO(
            totalProcessed: $totalProcessed,
            totalCreated: $totalCreated,
            totalUpdated: $totalUpdated,
            totalSkipped: $totalSkipped,
            errors: $errors,
        );
    }

    private function parseDateSafe(?string $raw): ?DateTimeImmutable
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $raw = trim($raw);

        // Format YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
            $dt = DateTimeImmutable::createFromFormat('Y-m-d', $raw);
            if ($dt !== false) {
                return $dt;
            }
        }

        // Format DD/MM/YYYY
        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $raw)) {
            $dt = DateTimeImmutable::createFromFormat('d/m/Y', $raw);
            if ($dt !== false) {
                return $dt;
            }
        }

        // Format DD-MM-YYYY
        if (preg_match('/^\d{1,2}-\d{1,2}-\d{4}$/', $raw)) {
            $dt = DateTimeImmutable::createFromFormat('d-m-Y', $raw);
            if ($dt !== false) {
                return $dt;
            }
        }

        try {
            return new DateTimeImmutable($raw);
        } catch (\Throwable) {
            return null;
        }
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

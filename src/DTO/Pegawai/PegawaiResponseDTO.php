<?php

declare(strict_types=1);

namespace App\DTO\Pegawai;

use App\Entity\Pegawai;

final readonly class PegawaiResponseDTO
{
    public function __construct(
        public int $id,
        public string $namaLengkap,
        public string $namaDenganGelar,
        public ?string $gelarDepan,
        public ?string $gelarBelakang,
        public ?string $nip,
        public ?string $nuptk,
        public ?string $nik,
        public string $jenisKelamin,
        public string $jenisKelaminLabel,
        public string $jenisKelaminBadge,
        public ?string $tempatLahir,
        public ?string $tanggalLahir,
        public ?string $agama,
        public ?string $alamat,
        public ?string $nomorHp,
        public ?string $email,
        public string $kategoriPegawai,
        public string $kategoriPegawaiLabel,
        public string $kategoriPegawaiBadge,
        public string $jenisPegawai,
        public string $jenisPegawaiLabel,
        public string $statusKepegawaian,
        public string $statusKepegawaianLabel,
        public string $statusKepegawaianBadge,
        public ?string $jabatan,
        public ?string $pendidikanTerakhir,
        public ?string $jurusanPendidikan,
        public ?string $tanggalMasuk,
        public ?string $fotoUrl,
        public bool $isActive,
        public ?int $userId,
        // Data khusus Guru
        public ?int $guruId,
        public ?string $bidangStudiUtama,
        public bool $isSertifikasi,
        public ?string $noSertifikasi,
        public ?string $tugasTambahan,
        public ?string $tmtPendidik,
        public string $createdAt,
    ) {}

    public static function fromEntity(Pegawai $pegawai): self
    {
        $guru = $pegawai->getGuru();
        $user = $pegawai->getUser();

        return new self(
            id: (int) $pegawai->getId(),
            namaLengkap: $pegawai->getNamaLengkap(),
            namaDenganGelar: $pegawai->getNamaDenganGelar(),
            gelarDepan: $pegawai->getGelarDepan(),
            gelarBelakang: $pegawai->getGelarBelakang(),
            nip: $pegawai->getNip(),
            nuptk: $pegawai->getNuptk(),
            nik: $pegawai->getNik(),
            jenisKelamin: $pegawai->getJenisKelamin()->value,
            jenisKelaminLabel: $pegawai->getJenisKelamin()->label(),
            jenisKelaminBadge: $pegawai->getJenisKelamin()->badgeClass(),
            tempatLahir: $pegawai->getTempatLahir(),
            tanggalLahir: $pegawai->getTanggalLahir()?->format('Y-m-d'),
            agama: $pegawai->getAgama(),
            alamat: $pegawai->getAlamat(),
            nomorHp: $pegawai->getNomorHp(),
            email: $pegawai->getEmail(),
            kategoriPegawai: $pegawai->getKategoriPegawai()->value,
            kategoriPegawaiLabel: $pegawai->getKategoriPegawai()->label(),
            kategoriPegawaiBadge: $pegawai->getKategoriPegawai()->badgeClass(),
            jenisPegawai: $pegawai->getJenisPegawai()->value,
            jenisPegawaiLabel: $pegawai->getJenisPegawai()->label(),
            statusKepegawaian: $pegawai->getStatusKepegawaian()->value,
            statusKepegawaianLabel: $pegawai->getStatusKepegawaian()->label(),
            statusKepegawaianBadge: $pegawai->getStatusKepegawaian()->badgeClass(),
            jabatan: $pegawai->getJabatan(),
            pendidikanTerakhir: $pegawai->getPendidikanTerakhir(),
            jurusanPendidikan: $pegawai->getJurusanPendidikan(),
            tanggalMasuk: $pegawai->getTanggalMasuk()?->format('Y-m-d'),
            fotoUrl: $pegawai->getFotoUrl(),
            isActive: $pegawai->isActive(),
            userId: $user !== null ? (int) $user->getId() : null,
            guruId: $guru !== null ? (int) $guru->getId() : null,
            bidangStudiUtama: $guru?->getBidangStudiUtama(),
            isSertifikasi: $guru?->isSertifikasi() ?? false,
            noSertifikasi: $guru?->getNoSertifikasi(),
            tugasTambahan: $guru?->getTugasTambahan(),
            tmtPendidik: $guru?->getTmtPendidik()?->format('Y-m-d'),
            createdAt: $pegawai->getCreatedAt()->format('Y-m-d H:i:s'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'namaLengkap' => $this->namaLengkap,
            'namaDenganGelar' => $this->namaDenganGelar,
            'gelarDepan' => $this->gelarDepan,
            'gelarBelakang' => $this->gelarBelakang,
            'nip' => $this->nip,
            'nuptk' => $this->nuptk,
            'nik' => $this->nik,
            'jenisKelamin' => $this->jenisKelamin,
            'jenisKelaminLabel' => $this->jenisKelaminLabel,
            'jenisKelaminBadge' => $this->jenisKelaminBadge,
            'tempatLahir' => $this->tempatLahir,
            'tanggalLahir' => $this->tanggalLahir,
            'agama' => $this->agama,
            'alamat' => $this->alamat,
            'nomorHp' => $this->nomorHp,
            'email' => $this->email,
            'kategoriPegawai' => $this->kategoriPegawai,
            'kategoriPegawaiLabel' => $this->kategoriPegawaiLabel,
            'kategoriPegawaiBadge' => $this->kategoriPegawaiBadge,
            'jenisPegawai' => $this->jenisPegawai,
            'jenisPegawaiLabel' => $this->jenisPegawaiLabel,
            'statusKepegawaian' => $this->statusKepegawaian,
            'statusKepegawaianLabel' => $this->statusKepegawaianLabel,
            'statusKepegawaianBadge' => $this->statusKepegawaianBadge,
            'jabatan' => $this->jabatan,
            'pendidikanTerakhir' => $this->pendidikanTerakhir,
            'jurusanPendidikan' => $this->jurusanPendidikan,
            'tanggalMasuk' => $this->tanggalMasuk,
            'fotoUrl' => $this->fotoUrl,
            'isActive' => $this->isActive,
            'userId' => $this->userId,
            'guruId' => $this->guruId,
            'bidangStudiUtama' => $this->bidangStudiUtama,
            'isSertifikasi' => $this->isSertifikasi,
            'noSertifikasi' => $this->noSertifikasi,
            'tugasTambahan' => $this->tugasTambahan,
            'tmtPendidik' => $this->tmtPendidik,
            'createdAt' => $this->createdAt,
        ];
    }
}

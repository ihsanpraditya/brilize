<?php

declare(strict_types=1);

namespace App\DTO\Mutasi;

use App\Entity\MutasiSiswa;

final readonly class MutasiResponseDTO
{
    public function __construct(
        public int $id,
        public int $siswaId,
        public string $siswaNama,
        public string $siswaNis,
        public ?string $siswaNisn,
        public int $tahunPelajaranId,
        public string $tahunPelajaranNama,
        public string $jenis,
        public string $jenisLabel,
        public string $jenisBadge,
        public ?int $kelasAsalId,
        public ?string $kelasAsalNama,
        public ?int $kelasTujuanId,
        public ?string $kelasTujuanNama,
        public string $tanggalMutasi,
        public ?string $nomorSurat,
        public ?string $sekolahAsalTujuan,
        public ?string $alasan,
        public string $createdAt,
    ) {}

    public static function fromEntity(MutasiSiswa $mutasi): self
    {
        $siswa = $mutasi->getSiswa();
        $tp = $mutasi->getTahunPelajaran();
        $ka = $mutasi->getKelasAsal();
        $kt = $mutasi->getKelasTujuan();

        return new self(
            id: (int) $mutasi->getId(),
            siswaId: (int) $siswa->getId(),
            siswaNama: $siswa->getNamaLengkap(),
            siswaNis: $siswa->getNis(),
            siswaNisn: $siswa->getNisn(),
            tahunPelajaranId: (int) $tp->getId(),
            tahunPelajaranNama: $tp->getNamaLengkap(),
            jenis: $mutasi->getJenis()->value,
            jenisLabel: $mutasi->getJenis()->label(),
            jenisBadge: $mutasi->getJenis()->badgeClass(),
            kelasAsalId: $ka !== null ? (int) $ka->getId() : null,
            kelasAsalNama: $ka?->getNamaKelas(),
            kelasTujuanId: $kt !== null ? (int) $kt->getId() : null,
            kelasTujuanNama: $kt?->getNamaKelas(),
            tanggalMutasi: $mutasi->getTanggalMutasi()->format('Y-m-d'),
            nomorSurat: $mutasi->getNomorSurat(),
            sekolahAsalTujuan: $mutasi->getSekolahAsalTujuan(),
            alasan: $mutasi->getAlasan(),
            createdAt: $mutasi->getCreatedAt()->format('Y-m-d H:i:s'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'siswaId' => $this->siswaId,
            'siswaNama' => $this->siswaNama,
            'siswaNis' => $this->siswaNis,
            'siswaNisn' => $this->siswaNisn,
            'tahunPelajaranId' => $this->tahunPelajaranId,
            'tahunPelajaranNama' => $this->tahunPelajaranNama,
            'jenis' => $this->jenis,
            'jenisLabel' => $this->jenisLabel,
            'jenisBadge' => $this->jenisBadge,
            'kelasAsalId' => $this->kelasAsalId,
            'kelasAsalNama' => $this->kelasAsalNama,
            'kelasTujuanId' => $this->kelasTujuanId,
            'kelasTujuanNama' => $this->kelasTujuanNama,
            'tanggalMutasi' => $this->tanggalMutasi,
            'nomorSurat' => $this->nomorSurat,
            'sekolahAsalTujuan' => $this->sekolahAsalTujuan,
            'alasan' => $this->alasan,
            'createdAt' => $this->createdAt,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\JenisMutasi;
use App\Repository\MutasiSiswaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MutasiSiswaRepository::class)]
#[ORM\Table(name: 'mutasi_siswa')]
#[ORM\Index(name: 'idx_mutasi_siswa_id', columns: ['siswa_id'])]
#[ORM\Index(name: 'idx_mutasi_tahun_id', columns: ['tahun_pelajaran_id'])]
#[ORM\Index(name: 'idx_mutasi_jenis', columns: ['jenis'])]
#[ORM\Index(name: 'idx_mutasi_tanggal', columns: ['tanggal_mutasi'])]
#[ORM\HasLifecycleCallbacks]
class MutasiSiswa
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Siswa::class)]
    #[ORM\JoinColumn(name: 'siswa_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Siswa $siswa;

    #[ORM\ManyToOne(targetEntity: TahunPelajaran::class)]
    #[ORM\JoinColumn(name: 'tahun_pelajaran_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    private TahunPelajaran $tahunPelajaran;

    #[ORM\Column(type: Types::STRING, length: 30, enumType: JenisMutasi::class)]
    private JenisMutasi $jenis;

    #[ORM\ManyToOne(targetEntity: Kelas::class)]
    #[ORM\JoinColumn(name: 'kelas_asal_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Kelas $kelasAsal = null;

    #[ORM\ManyToOne(targetEntity: Kelas::class)]
    #[ORM\JoinColumn(name: 'kelas_tujuan_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Kelas $kelasTujuan = null;

    #[ORM\Column(name: 'tanggal_mutasi', type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $tanggalMutasi;

    #[ORM\Column(name: 'nomor_surat', type: Types::STRING, length: 100, nullable: true)]
    private ?string $nomorSurat = null;

    #[ORM\Column(name: 'sekolah_asal_tujuan', type: Types::STRING, length: 150, nullable: true)]
    private ?string $sekolahAsalTujuan = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $alasan = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->tanggalMutasi = new \DateTimeImmutable();
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        if (!isset($this->createdAt)) {
            $this->createdAt = new \DateTimeImmutable();
        }
        if (!isset($this->tanggalMutasi)) {
            $this->tanggalMutasi = new \DateTimeImmutable();
        }
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSiswa(): Siswa
    {
        return $this->siswa;
    }

    public function setSiswa(Siswa $siswa): self
    {
        $this->siswa = $siswa;
        return $this;
    }

    public function getTahunPelajaran(): TahunPelajaran
    {
        return $this->tahunPelajaran;
    }

    public function setTahunPelajaran(TahunPelajaran $tahunPelajaran): self
    {
        $this->tahunPelajaran = $tahunPelajaran;
        return $this;
    }

    public function getJenis(): JenisMutasi
    {
        return $this->jenis;
    }

    public function setJenis(JenisMutasi $jenis): self
    {
        $this->jenis = $jenis;
        return $this;
    }

    public function getKelasAsal(): ?Kelas
    {
        return $this->kelasAsal;
    }

    public function setKelasAsal(?Kelas $kelasAsal): self
    {
        $this->kelasAsal = $kelasAsal;
        return $this;
    }

    public function getKelasTujuan(): ?Kelas
    {
        return $this->kelasTujuan;
    }

    public function setKelasTujuan(?Kelas $kelasTujuan): self
    {
        $this->kelasTujuan = $kelasTujuan;
        return $this;
    }

    public function getTanggalMutasi(): \DateTimeImmutable
    {
        return $this->tanggalMutasi;
    }

    public function setTanggalMutasi(\DateTimeImmutable $tanggalMutasi): self
    {
        $this->tanggalMutasi = $tanggalMutasi;
        return $this;
    }

    public function getNomorSurat(): ?string
    {
        return $this->nomorSurat;
    }

    public function setNomorSurat(?string $nomorSurat): self
    {
        $this->nomorSurat = $nomorSurat !== null ? trim($nomorSurat) : null;
        return $this;
    }

    public function getSekolahAsalTujuan(): ?string
    {
        return $this->sekolahAsalTujuan;
    }

    public function setSekolahAsalTujuan(?string $sekolahAsalTujuan): self
    {
        $this->sekolahAsalTujuan = $sekolahAsalTujuan !== null ? trim($sekolahAsalTujuan) : null;
        return $this;
    }

    public function getAlasan(): ?string
    {
        return $this->alasan;
    }

    public function setAlasan(?string $alasan): self
    {
        $this->alasan = $alasan !== null ? trim($alasan) : null;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\KelasRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: KelasRepository::class)]
#[ORM\Table(name: 'kelas')]
#[ORM\UniqueConstraint(name: 'uniq_kelas_nama_tahun', columns: ['nama_kelas', 'tahun_pelajaran_id'])]
#[ORM\Index(name: 'idx_kelas_tingkat', columns: ['tingkat'])]
#[ORM\Index(name: 'idx_kelas_tahun_pelajaran', columns: ['tahun_pelajaran_id'])]
#[ORM\Index(name: 'idx_kelas_wali', columns: ['wali_kelas_id'])]
#[ORM\HasLifecycleCallbacks]
class Kelas
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    /**
     * Nama Rombel/Kelas, contoh: "X-RPL-1", "XI-IPA-2", "VII-A"
     */
    #[ORM\Column(name: 'nama_kelas', type: Types::STRING, length: 50)]
    private string $namaKelas;

    /**
     * Tingkat jenjang kelas, contoh: 10, 11, 12 (SMA/SMK) atau 7, 8, 9 (SMP)
     */
    #[ORM\Column(type: Types::INTEGER)]
    private int $tingkat;

    /**
     * Jurusan / Peminatan, contoh: "Rekayasa Perangkat Lunak", "IPA", "IPS"
     */
    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $jurusan = null;

    #[ORM\ManyToOne(targetEntity: TahunPelajaran::class)]
    #[ORM\JoinColumn(name: 'tahun_pelajaran_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?TahunPelajaran $tahunPelajaran = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'wali_kelas_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $waliKelas = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 36])]
    private int $kapasitas = 36;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $keterangan = null;

    #[ORM\Column(name: 'is_active', type: Types::BOOLEAN, options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->kapasitas = 36;
        $this->isActive = true;
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        if (!isset($this->createdAt)) {
            $this->createdAt = new \DateTimeImmutable();
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

    public function getNamaKelas(): string
    {
        return $this->namaKelas;
    }

    public function setNamaKelas(string $namaKelas): self
    {
        $this->namaKelas = trim($namaKelas);
        return $this;
    }

    public function getTingkat(): int
    {
        return $this->tingkat;
    }

    public function setTingkat(int $tingkat): self
    {
        $this->tingkat = $tingkat;
        return $this;
    }

    public function getJurusan(): ?string
    {
        return $this->jurusan;
    }

    public function setJurusan(?string $jurusan): self
    {
        $this->jurusan = $jurusan !== null ? trim($jurusan) : null;
        return $this;
    }

    public function getTahunPelajaran(): ?TahunPelajaran
    {
        return $this->tahunPelajaran;
    }

    public function setTahunPelajaran(?TahunPelajaran $tahunPelajaran): self
    {
        $this->tahunPelajaran = $tahunPelajaran;
        return $this;
    }

    public function getWaliKelas(): ?User
    {
        return $this->waliKelas;
    }

    public function setWaliKelas(?User $waliKelas): self
    {
        $this->waliKelas = $waliKelas;
        return $this;
    }

    public function getKapasitas(): int
    {
        return $this->kapasitas;
    }

    public function setKapasitas(int $kapasitas): self
    {
        $this->kapasitas = $kapasitas;
        return $this;
    }

    public function getKeterangan(): ?string
    {
        return $this->keterangan;
    }

    public function setKeterangan(?string $keterangan): self
    {
        $this->keterangan = $keterangan !== null ? trim($keterangan) : null;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
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

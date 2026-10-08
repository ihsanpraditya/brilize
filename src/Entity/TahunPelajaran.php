<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\Semester;
use App\Repository\TahunPelajaranRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TahunPelajaranRepository::class)]
#[ORM\Table(name: 'tahun_pelajaran')]
#[ORM\UniqueConstraint(name: 'uniq_tahun_semester', columns: ['tahun', 'semester'])]
#[ORM\Index(name: 'idx_tahun_pelajaran_active', columns: ['is_active'])]
#[ORM\HasLifecycleCallbacks]
class TahunPelajaran
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $id = null;

    /**
     * Format: "2026/2027"
     */
    #[ORM\Column(type: Types::STRING, length: 20)]
    private string $tahun;

    #[ORM\Column(type: Types::STRING, length: 10, enumType: Semester::class)]
    private Semester $semester = Semester::GANJIL;

    #[ORM\Column(name: 'is_active', type: Types::BOOLEAN)]
    private bool $isActive = false;

    #[ORM\Column(name: 'tanggal_mulai', type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $tanggalMulai = null;

    #[ORM\Column(name: 'tanggal_selesai', type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $tanggalSelesai = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
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

    public function getTahun(): string
    {
        return $this->tahun;
    }

    public function setTahun(string $tahun): self
    {
        $this->tahun = trim($tahun);
        return $this;
    }

    public function getSemester(): Semester
    {
        return $this->semester;
    }

    public function setSemester(Semester $semester): self
    {
        $this->semester = $semester;
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

    public function getTanggalMulai(): ?\DateTimeImmutable
    {
        return $this->tanggalMulai;
    }

    public function setTanggalMulai(?\DateTimeImmutable $tanggalMulai): self
    {
        $this->tanggalMulai = $tanggalMulai;
        return $this;
    }

    public function getTanggalSelesai(): ?\DateTimeImmutable
    {
        return $this->tanggalSelesai;
    }

    public function setTanggalSelesai(?\DateTimeImmutable $tanggalSelesai): self
    {
        $this->tanggalSelesai = $tanggalSelesai;
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

    /**
     * Helper string lengkap, contoh: "2026/2027 - Ganjil"
     */
    public function getNamaLengkap(): string
    {
        return sprintf('%s - %s', $this->tahun, $this->semester->label());
    }
}

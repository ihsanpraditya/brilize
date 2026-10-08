<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\JenisKepegawaian;
use App\Enum\JenisKelamin;
use App\Enum\StatusKepegawaian;
use App\Repository\GuruRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GuruRepository::class)]
#[ORM\Table(name: 'guru')]
#[ORM\Index(name: 'idx_guru_nip', columns: ['nip'])]
#[ORM\Index(name: 'idx_guru_nuptk', columns: ['nuptk'])]
#[ORM\Index(name: 'idx_guru_status_pegawai', columns: ['status_kepegawaian'])]
#[ORM\Index(name: 'idx_guru_jenis_pegawai', columns: ['jenis_kepegawaian'])]
#[ORM\Index(name: 'idx_guru_is_active', columns: ['is_active'])]
#[ORM\HasLifecycleCallbacks]
class Guru
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    /**
     * Akun User untuk autentikasi sistem (opsional jika belum dibuatkan login)
     */
    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    /**
     * Nomor Induk Pegawai (PNS/PPPK)
     */
    #[ORM\Column(type: Types::STRING, length: 30, unique: true, nullable: true)]
    private ?string $nip = null;

    /**
     * Nomor Unik Pendidik dan Tenaga Kependidikan (Kemendikbud)
     */
    #[ORM\Column(type: Types::STRING, length: 30, unique: true, nullable: true)]
    private ?string $nuptk = null;

    /**
     * Nomor Induk Kependudukan (KTP)
     */
    #[ORM\Column(type: Types::STRING, length: 20, unique: true, nullable: true)]
    private ?string $nik = null;

    #[ORM\Column(name: 'nama_lengkap', type: Types::STRING, length: 150)]
    private string $namaLengkap;

    #[ORM\Column(name: 'gelar_depan', type: Types::STRING, length: 20, nullable: true)]
    private ?string $gelarDepan = null;

    #[ORM\Column(name: 'gelar_belakang', type: Types::STRING, length: 50, nullable: true)]
    private ?string $gelarBelakang = null;

    #[ORM\Column(name: 'jenis_kelamin', type: Types::STRING, length: 1, enumType: JenisKelamin::class)]
    private JenisKelamin $jenisKelamin = JenisKelamin::LAKI_LAKI;

    #[ORM\Column(name: 'tempat_lahir', type: Types::STRING, length: 100, nullable: true)]
    private ?string $tempatLahir = null;

    #[ORM\Column(name: 'tanggal_lahir', type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $tanggalLahir = null;

    #[ORM\Column(type: Types::STRING, length: 30, nullable: true)]
    private ?string $agama = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $alamat = null;

    #[ORM\Column(name: 'nomor_hp', type: Types::STRING, length: 20, nullable: true)]
    private ?string $nomorHp = null;

    #[ORM\Column(type: Types::STRING, length: 180, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(name: 'jenis_kepegawaian', type: Types::STRING, length: 30, enumType: JenisKepegawaian::class)]
    private JenisKepegawaian $jenisKepegawaian = JenisKepegawaian::GURU_MAPEL;

    #[ORM\Column(name: 'status_kepegawaian', type: Types::STRING, length: 20, enumType: StatusKepegawaian::class)]
    private StatusKepegawaian $statusKepegawaian = StatusKepegawaian::HONORER;

    #[ORM\Column(name: 'pendidikan_terakhir', type: Types::STRING, length: 50, nullable: true)]
    private ?string $pendidikanTerakhir = 'S1';

    #[ORM\Column(name: 'jurusan_pendidikan', type: Types::STRING, length: 100, nullable: true)]
    private ?string $jurusanPendidikan = null;

    #[ORM\Column(name: 'foto_url', type: Types::STRING, length: 255, nullable: true)]
    private ?string $fotoUrl = null;

    #[ORM\Column(name: 'is_active', type: Types::BOOLEAN, options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->jenisKelamin = JenisKelamin::LAKI_LAKI;
        $this->jenisKepegawaian = JenisKepegawaian::GURU_MAPEL;
        $this->statusKepegawaian = StatusKepegawaian::HONORER;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getNip(): ?string
    {
        return $this->nip;
    }

    public function setNip(?string $nip): self
    {
        $this->nip = $nip !== null && trim($nip) !== '' ? trim($nip) : null;
        return $this;
    }

    public function getNuptk(): ?string
    {
        return $this->nuptk;
    }

    public function setNuptk(?string $nuptk): self
    {
        $this->nuptk = $nuptk !== null && trim($nuptk) !== '' ? trim($nuptk) : null;
        return $this;
    }

    public function getNik(): ?string
    {
        return $this->nik;
    }

    public function setNik(?string $nik): self
    {
        $this->nik = $nik !== null && trim($nik) !== '' ? trim($nik) : null;
        return $this;
    }

    public function getNamaLengkap(): string
    {
        return $this->namaLengkap;
    }

    public function setNamaLengkap(string $namaLengkap): self
    {
        $this->namaLengkap = trim($namaLengkap);
        return $this;
    }

    public function getGelarDepan(): ?string
    {
        return $this->gelarDepan;
    }

    public function setGelarDepan(?string $gelarDepan): self
    {
        $this->gelarDepan = $gelarDepan !== null && trim($gelarDepan) !== '' ? trim($gelarDepan) : null;
        return $this;
    }

    public function getGelarBelakang(): ?string
    {
        return $this->gelarBelakang;
    }

    public function setGelarBelakang(?string $gelarBelakang): self
    {
        $this->gelarBelakang = $gelarBelakang !== null && trim($gelarBelakang) !== '' ? trim($gelarBelakang) : null;
        return $this;
    }

    public function getNamaDenganGelar(): string
    {
        $nama = $this->namaLengkap;
        if ($this->gelarDepan) {
            $nama = $this->gelarDepan . ' ' . $nama;
        }
        if ($this->gelarBelakang) {
            $nama = $nama . ', ' . $this->gelarBelakang;
        }
        return $nama;
    }

    public function getJenisKelamin(): JenisKelamin
    {
        return $this->jenisKelamin;
    }

    public function setJenisKelamin(JenisKelamin $jenisKelamin): self
    {
        $this->jenisKelamin = $jenisKelamin;
        return $this;
    }

    public function getTempatLahir(): ?string
    {
        return $this->tempatLahir;
    }

    public function setTempatLahir(?string $tempatLahir): self
    {
        $this->tempatLahir = $tempatLahir;
        return $this;
    }

    public function getTanggalLahir(): ?\DateTimeImmutable
    {
        return $this->tanggalLahir;
    }

    public function setTanggalLahir(?\DateTimeImmutable $tanggalLahir): self
    {
        $this->tanggalLahir = $tanggalLahir;
        return $this;
    }

    public function getAgama(): ?string
    {
        return $this->agama;
    }

    public function setAgama(?string $agama): self
    {
        $this->agama = $agama;
        return $this;
    }

    public function getAlamat(): ?string
    {
        return $this->alamat;
    }

    public function setAlamat(?string $alamat): self
    {
        $this->alamat = $alamat;
        return $this;
    }

    public function getNomorHp(): ?string
    {
        return $this->nomorHp;
    }

    public function setNomorHp(?string $nomorHp): self
    {
        $this->nomorHp = $nomorHp;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email !== null ? strtolower(trim($email)) : null;
        return $this;
    }

    public function getJenisKepegawaian(): JenisKepegawaian
    {
        return $this->jenisKepegawaian;
    }

    public function setJenisKepegawaian(JenisKepegawaian $jenisKepegawaian): self
    {
        $this->jenisKepegawaian = $jenisKepegawaian;
        return $this;
    }

    public function getStatusKepegawaian(): StatusKepegawaian
    {
        return $this->statusKepegawaian;
    }

    public function setStatusKepegawaian(StatusKepegawaian $statusKepegawaian): self
    {
        $this->statusKepegawaian = $statusKepegawaian;
        return $this;
    }

    public function getPendidikanTerakhir(): ?string
    {
        return $this->pendidikanTerakhir;
    }

    public function setPendidikanTerakhir(?string $pendidikanTerakhir): self
    {
        $this->pendidikanTerakhir = $pendidikanTerakhir;
        return $this;
    }

    public function getJurusanPendidikan(): ?string
    {
        return $this->jurusanPendidikan;
    }

    public function setJurusanPendidikan(?string $jurusanPendidikan): self
    {
        $this->jurusanPendidikan = $jurusanPendidikan;
        return $this;
    }

    public function getFotoUrl(): ?string
    {
        return $this->fotoUrl;
    }

    public function setFotoUrl(?string $fotoUrl): self
    {
        $this->fotoUrl = $fotoUrl;
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

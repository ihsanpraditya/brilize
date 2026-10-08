<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\JenisKelamin;
use App\Enum\JenisPegawai;
use App\Enum\KategoriPegawai;
use App\Enum\StatusKepegawaian;
use App\Repository\PegawaiRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PegawaiRepository::class)]
#[ORM\Table(name: 'pegawai')]
#[ORM\Index(name: 'idx_pegawai_nip', columns: ['nip'])]
#[ORM\Index(name: 'idx_pegawai_nuptk', columns: ['nuptk'])]
#[ORM\Index(name: 'idx_pegawai_kategori', columns: ['kategori_pegawai'])]
#[ORM\Index(name: 'idx_pegawai_jenis', columns: ['jenis_pegawai'])]
#[ORM\Index(name: 'idx_pegawai_status', columns: ['status_kepegawaian'])]
#[ORM\Index(name: 'idx_pegawai_is_active', columns: ['is_active'])]
#[ORM\HasLifecycleCallbacks]
class Pegawai
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    /**
     * Akun User untuk autentikasi sistem aplikasi (opsional)
     */
    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    /**
     * Relasi ke spesialisasi akademik Guru jika pegawai adalah tenaga pendidik
     */
    #[ORM\OneToOne(targetEntity: Guru::class, mappedBy: 'pegawai', cascade: ['persist', 'remove'])]
    private ?Guru $guru = null;

    /**
     * Nomor Induk Pegawai (PNS/PPPK)
     */
    #[ORM\Column(type: Types::STRING, length: 30, unique: true, nullable: true)]
    private ?string $nip = null;

    /**
     * Nomor Unik Pendidik dan Tenaga Kependidikan
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

    /**
     * Kategori: Pendidik (Guru) atau Tenaga Kependidikan (Tendik / Non-Guru)
     */
    #[ORM\Column(name: 'kategori_pegawai', type: Types::STRING, length: 30, enumType: KategoriPegawai::class)]
    private KategoriPegawai $kategoriPegawai = KategoriPegawai::TENAGA_KEPENDIDIKAN;

    /**
     * Jenis jabatan spesifik (Guru, Tata Usaha, Bendahara, Satpam, dll)
     */
    #[ORM\Column(name: 'jenis_pegawai', type: Types::STRING, length: 30, enumType: JenisPegawai::class)]
    private JenisPegawai $jenisPegawai = JenisPegawai::TATA_USAHA;

    /**
     * Status Kepegawaian (PNS, PPPK, GTY, GTT, HONORER)
     */
    #[ORM\Column(name: 'status_kepegawaian', type: Types::STRING, length: 20, enumType: StatusKepegawaian::class)]
    private StatusKepegawaian $statusKepegawaian = StatusKepegawaian::HONORER;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $jabatan = null;

    #[ORM\Column(name: 'pendidikan_terakhir', type: Types::STRING, length: 50, nullable: true)]
    private ?string $pendidikanTerakhir = 'S1';

    #[ORM\Column(name: 'jurusan_pendidikan', type: Types::STRING, length: 100, nullable: true)]
    private ?string $jurusanPendidikan = null;

    #[ORM\Column(name: 'tanggal_masuk', type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $tanggalMasuk = null;

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
        $this->kategoriPegawai = KategoriPegawai::TENAGA_KEPENDIDIKAN;
        $this->jenisPegawai = JenisPegawai::TATA_USAHA;
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

    public function getGuru(): ?Guru
    {
        return $this->guru;
    }

    public function setGuru(?Guru $guru): self
    {
        $this->guru = $guru;
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

    public function getKategoriPegawai(): KategoriPegawai
    {
        return $this->kategoriPegawai;
    }

    public function setKategoriPegawai(KategoriPegawai $kategoriPegawai): self
    {
        $this->kategoriPegawai = $kategoriPegawai;
        return $this;
    }

    public function getJenisPegawai(): JenisPegawai
    {
        return $this->jenisPegawai;
    }

    public function setJenisPegawai(JenisPegawai $jenisPegawai): self
    {
        $this->jenisPegawai = $jenisPegawai;
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

    public function getJabatan(): ?string
    {
        return $this->jabatan;
    }

    public function setJabatan(?string $jabatan): self
    {
        $this->jabatan = $jabatan;
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

    public function getTanggalMasuk(): ?\DateTimeImmutable
    {
        return $this->tanggalMasuk;
    }

    public function setTanggalMasuk(?\DateTimeImmutable $tanggalMasuk): self
    {
        $this->tanggalMasuk = $tanggalMasuk;
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

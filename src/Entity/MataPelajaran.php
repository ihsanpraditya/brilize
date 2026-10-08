<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\KelompokMapel;
use App\Repository\MataPelajaranRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MataPelajaranRepository::class)]
#[ORM\Table(name: 'mata_pelajaran')]
#[ORM\Index(name: 'idx_mapel_kode', columns: ['kode_mapel'])]
#[ORM\Index(name: 'idx_mapel_kelompok', columns: ['kelompok'])]
#[ORM\Index(name: 'idx_mapel_tingkat', columns: ['tingkat'])]
#[ORM\Index(name: 'idx_mapel_is_active', columns: ['is_active'])]
#[ORM\HasLifecycleCallbacks]
class MataPelajaran
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    /**
     * Kode Unik Mata Pelajaran, contoh: "MTK-10", "BIG-11", "RPL-PWPB"
     */
    #[ORM\Column(name: 'kode_mapel', type: Types::STRING, length: 30, unique: true)]
    private string $kodeMapel;

    /**
     * Nama Lengkap Mata Pelajaran, contoh: "Matematika", "Bahasa Inggris Lanjut"
     */
    #[ORM\Column(name: 'nama_mapel', type: Types::STRING, length: 150)]
    private string $namaMapel;

    /**
     * Kelompok Kurikulum (Kelompok A Wajib, B Umum, C Peminatan/Kejuruan, Mulok, BK)
     */
    #[ORM\Column(type: Types::STRING, length: 30, enumType: KelompokMapel::class)]
    private KelompokMapel $kelompok = KelompokMapel::WAJIB;

    /**
     * Tingkat jenjang kelas (10, 11, 12 atau null jika berlaku untuk semua tingkat)
     */
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $tingkat = null;

    /**
     * Jurusan/Peminatan spesifik (contoh: "Rekayasa Perangkat Lunak", "MIPA")
     */
    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $jurusan = null;

    /**
     * Kriteria Ketuntasan Minimal (KKM) / KKTP
     */
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 75])]
    private int $kkm = 75;

    /**
     * Alokasi Beban Jam Pelajaran (JP) per minggu
     */
    #[ORM\Column(name: 'beban_jam_per_minggu', type: Types::INTEGER, options: ['default' => 2])]
    private int $bebanJamPerMinggu = 2;

    /**
     * Daftar Guru Pengampu Mata Pelajaran (Relasi Many-to-Many)
     * 1 Mapel dapat diampu oleh banyak Guru, dan 1 Guru dapat mengajar banyak Mapel
     *
     * @var Collection<int, Guru>
     */
    #[ORM\ManyToMany(targetEntity: Guru::class, inversedBy: 'mataPelajaranList')]
    #[ORM\JoinTable(name: 'guru_mata_pelajaran')]
    #[ORM\JoinColumn(name: 'mata_pelajaran_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'guru_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $guruPengampuList;

    /**
     * Guru Koordinator Mapel (Opsional)
     */
    #[ORM\ManyToOne(targetEntity: Guru::class)]
    #[ORM\JoinColumn(name: 'guru_koordinator_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Guru $guruKoordinator = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $urutan = 0;

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
        $this->kelompok = KelompokMapel::WAJIB;
        $this->kkm = 75;
        $this->bebanJamPerMinggu = 2;
        $this->urutan = 0;
        $this->isActive = true;
        $this->guruPengampuList = new ArrayCollection();
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

    public function getKodeMapel(): string
    {
        return $this->kodeMapel;
    }

    public function setKodeMapel(string $kodeMapel): self
    {
        $this->kodeMapel = strtoupper(trim($kodeMapel));
        return $this;
    }

    public function getNamaMapel(): string
    {
        return $this->namaMapel;
    }

    public function setNamaMapel(string $namaMapel): self
    {
        $this->namaMapel = trim($namaMapel);
        return $this;
    }

    public function getKelompok(): KelompokMapel
    {
        return $this->kelompok;
    }

    public function setKelompok(KelompokMapel $kelompok): self
    {
        $this->kelompok = $kelompok;
        return $this;
    }

    public function getTingkat(): ?int
    {
        return $this->tingkat;
    }

    public function setTingkat(?int $tingkat): self
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

    public function getKkm(): int
    {
        return $this->kkm;
    }

    public function setKkm(int $kkm): self
    {
        $this->kkm = $kkm;
        return $this;
    }

    public function getBebanJamPerMinggu(): int
    {
        return $this->bebanJamPerMinggu;
    }

    public function setBebanJamPerMinggu(int $bebanJamPerMinggu): self
    {
        $this->bebanJamPerMinggu = $bebanJamPerMinggu;
        return $this;
    }

    /**
     * @return Collection<int, Guru>
     */
    public function getGuruPengampuList(): Collection
    {
        return $this->guruPengampuList;
    }

    public function addGuruPengampu(Guru $guru): self
    {
        if (!$this->guruPengampuList->contains($guru)) {
            $this->guruPengampuList->add($guru);
            $guru->addMataPelajaran($this);
        }

        return $this;
    }

    public function removeGuruPengampu(Guru $guru): self
    {
        if ($this->guruPengampuList->removeElement($guru)) {
            $guru->removeMataPelajaran($this);
        }

        return $this;
    }

    public function getGuruKoordinator(): ?Guru
    {
        return $this->guruKoordinator;
    }

    public function setGuruKoordinator(?Guru $guruKoordinator): self
    {
        $this->guruKoordinator = $guruKoordinator;
        return $this;
    }

    public function getUrutan(): int
    {
        return $this->urutan;
    }

    public function setUrutan(int $urutan): self
    {
        $this->urutan = $urutan;
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

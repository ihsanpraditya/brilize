<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\JenisKelamin;
use App\Repository\GuruRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GuruRepository::class)]
#[ORM\Table(name: 'guru')]
#[ORM\Index(name: 'idx_guru_pegawai', columns: ['pegawai_id'])]
#[ORM\Index(name: 'idx_guru_bidang_studi', columns: ['bidang_studi_utama'])]
#[ORM\HasLifecycleCallbacks]
class Guru
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    /**
     * Relasi ke entitas induk Pegawai (biodata & status kepegawaian)
     */
    #[ORM\OneToOne(targetEntity: Pegawai::class, inversedBy: 'guru')]
    #[ORM\JoinColumn(name: 'pegawai_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Pegawai $pegawai;

    /**
     * Daftar Mata Pelajaran yang diampu oleh Guru (Relasi Many-to-Many)
     * 1 Guru dapat mengajar banyak Mapel, dan 1 Mapel dapat diampu banyak Guru
     *
     * @var Collection<int, MataPelajaran>
     */
    #[ORM\ManyToMany(targetEntity: MataPelajaran::class, mappedBy: 'guruPengampuList')]
    private Collection $mataPelajaranList;

    /**
     * Bidang studi / mata pelajaran yang diampu, contoh: "Matematika", "Bahasa Inggris"
     */
    #[ORM\Column(name: 'bidang_studi_utama', type: Types::STRING, length: 100, nullable: true)]
    private ?string $bidangStudiUtama = null;

    /**
     * Status kelulusan sertifikasi pendidik (PPG / TPG)
     */
    #[ORM\Column(name: 'is_sertifikasi', type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isSertifikasi = false;

    /**
     * Nomor Registrasi Guru (NRG) atau No. Peserta Sertifikasi
     */
    #[ORM\Column(name: 'no_sertifikasi', type: Types::STRING, length: 50, nullable: true)]
    private ?string $noSertifikasi = null;

    /**
     * Tugas tambahan, contoh: "Wali Kelas X-RPL-1", "Kepala Bengkel RPL", "Pembina OSIS"
     */
    #[ORM\Column(name: 'tugas_tambahan', type: Types::STRING, length: 100, nullable: true)]
    private ?string $tugasTambahan = null;

    /**
     * Terhitung Mulai Tanggal (TMT) awal mengajar
     */
    #[ORM\Column(name: 'tmt_pendidik', type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $tmtPendidik = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->isSertifikasi = false;
        $this->mataPelajaranList = new ArrayCollection();
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

    public function getPegawai(): Pegawai
    {
        return $this->pegawai;
    }

    public function setPegawai(Pegawai $pegawai): self
    {
        $this->pegawai = $pegawai;
        return $this;
    }

    /**
     * @return Collection<int, MataPelajaran>
     */
    public function getMataPelajaranList(): Collection
    {
        return $this->mataPelajaranList;
    }

    public function addMataPelajaran(MataPelajaran $mapel): self
    {
        if (!$this->mataPelajaranList->contains($mapel)) {
            $this->mataPelajaranList->add($mapel);
            $mapel->addGuruPengampu($this);
        }

        return $this;
    }

    public function removeMataPelajaran(MataPelajaran $mapel): self
    {
        if ($this->mataPelajaranList->removeElement($mapel)) {
            $mapel->removeGuruPengampu($this);
        }

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getDaftarNamaMapel(): array
    {
        return $this->mataPelajaranList->map(fn(MataPelajaran $m) => $m->getNamaMapel())->toArray();
    }

    public function getBidangStudiUtama(): ?string
    {
        return $this->bidangStudiUtama;
    }

    public function setBidangStudiUtama(?string $bidangStudiUtama): self
    {
        $this->bidangStudiUtama = $bidangStudiUtama !== null ? trim($bidangStudiUtama) : null;
        return $this;
    }

    public function isSertifikasi(): bool
    {
        return $this->isSertifikasi;
    }

    public function setIsSertifikasi(bool $isSertifikasi): self
    {
        $this->isSertifikasi = $isSertifikasi;
        return $this;
    }

    public function getNoSertifikasi(): ?string
    {
        return $this->noSertifikasi;
    }

    public function setNoSertifikasi(?string $noSertifikasi): self
    {
        $this->noSertifikasi = $noSertifikasi !== null ? trim($noSertifikasi) : null;
        return $this;
    }

    public function getTugasTambahan(): ?string
    {
        return $this->tugasTambahan;
    }

    public function setTugasTambahan(?string $tugasTambahan): self
    {
        $this->tugasTambahan = $tugasTambahan !== null ? trim($tugasTambahan) : null;
        return $this;
    }

    public function getTmtPendidik(): ?\DateTimeImmutable
    {
        return $this->tmtPendidik;
    }

    public function setTmtPendidik(?\DateTimeImmutable $tmtPendidik): self
    {
        $this->tmtPendidik = $tmtPendidik;
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

    // Helper proxy methods ke Pegawai
    public function getNamaLengkap(): string
    {
        return $this->pegawai->getNamaLengkap();
    }

    public function getNamaDenganGelar(): string
    {
        return $this->pegawai->getNamaDenganGelar();
    }

    public function getNip(): ?string
    {
        return $this->pegawai->getNip();
    }

    public function getNuptk(): ?string
    {
        return $this->pegawai->getNuptk();
    }

    public function getUser(): ?User
    {
        return $this->pegawai->getUser();
    }

    public function getJenisKelamin(): JenisKelamin
    {
        return $this->pegawai->getJenisKelamin();
    }
}

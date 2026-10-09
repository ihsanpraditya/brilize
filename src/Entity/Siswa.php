<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\JenisKelamin;
use App\Enum\StatusSiswa;
use App\Repository\SiswaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SiswaRepository::class)]
#[ORM\Table(name: 'siswa')]
#[ORM\Index(name: 'idx_siswa_nis', columns: ['nis'])]
#[ORM\Index(name: 'idx_siswa_nisn', columns: ['nisn'])]
#[ORM\Index(name: 'idx_siswa_nik', columns: ['nik'])]
#[ORM\Index(name: 'idx_siswa_kelas', columns: ['kelas_id'])]
#[ORM\Index(name: 'idx_siswa_status', columns: ['status'])]
#[ORM\Index(name: 'idx_siswa_is_active', columns: ['is_active'])]
#[ORM\HasLifecycleCallbacks]
class Siswa
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    /**
     * Akun User untuk autentikasi portal siswa/wali (opsional)
     */
    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    /**
     * Nomor Induk Siswa lokal sekolah
     */
    #[ORM\Column(type: Types::STRING, length: 30, unique: true)]
    private string $nis;

    /**
     * Nomor Induk Siswa Nasional (Dapodik / Kemendikbud)
     */
    #[ORM\Column(type: Types::STRING, length: 10, unique: true, nullable: true)]
    private ?string $nisn = null;

    /**
     * Nomor Induk Kependudukan (NIK)
     */
    #[ORM\Column(type: Types::STRING, length: 16, unique: true, nullable: true)]
    private ?string $nik = null;

    #[ORM\Column(name: 'nama_lengkap', type: Types::STRING, length: 150)]
    private string $namaLengkap;

    #[ORM\Column(name: 'nama_panggilan', type: Types::STRING, length: 50, nullable: true)]
    private ?string $namaPanggilan = null;

    /**
     * Nama Siswa dalam aksara/bahasa Arab (opsional, untuk pesantren/madrasah/sekolah Islam)
     */
    #[ORM\Column(name: 'nama_arab', type: Types::STRING, length: 150, nullable: true)]
    private ?string $namaArab = null;

    #[ORM\Column(name: 'jenis_kelamin', type: Types::STRING, length: 1, enumType: JenisKelamin::class)]
    private JenisKelamin $jenisKelamin = JenisKelamin::LAKI_LAKI;

    #[ORM\Column(name: 'tempat_lahir', type: Types::STRING, length: 100, nullable: true)]
    private ?string $tempatLahir = null;

    #[ORM\Column(name: 'tanggal_lahir', type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $tanggalLahir = null;

    #[ORM\Column(type: Types::STRING, length: 10, nullable: true)]
    private ?string $agama = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $alamat = null;

    #[ORM\Column(type: Types::STRING, length: 5, nullable: true)]
    private ?string $rt = null;

    #[ORM\Column(type: Types::STRING, length: 5, nullable: true)]
    private ?string $rw = null;

    #[ORM\Column(type: Types::STRING, length: 30, nullable: true)]
    private ?string $kelurahan = null;

    #[ORM\Column(type: Types::STRING, length: 30, nullable: true)]
    private ?string $kecamatan = null;

    #[ORM\Column(name: 'kabupaten_kota', type: Types::STRING, length: 30, nullable: true)]
    private ?string $kabupatenKota = null;

    #[ORM\Column(type: Types::STRING, length: 30, nullable: true)]
    private ?string $provinsi = null;

    #[ORM\Column(name: 'kode_pos', type: Types::STRING, length: 5, nullable: true)]
    private ?string $kodePos = null;

    #[ORM\Column(name: 'nomor_hp', type: Types::STRING, length: 20, nullable: true)]
    private ?string $nomorHp = null;

    #[ORM\Column(type: Types::STRING, length: 180, nullable: true)]
    private ?string $email = null;

    /**
     * Rombel / Kelas siswa saat ini
     */
    #[ORM\ManyToOne(targetEntity: Kelas::class)]
    #[ORM\JoinColumn(name: 'kelas_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Kelas $kelas = null;

    /**
     * Status kesiswaan (Aktif, Lulus, Mutasi, Keluar, Nonaktif)
     */
    #[ORM\Column(type: Types::STRING, length: 20, enumType: StatusSiswa::class)]
    private StatusSiswa $status = StatusSiswa::AKTIF;

    // --- Data Orang Tua & Wali ---

    #[ORM\Column(name: 'nama_ayah', type: Types::STRING, length: 150, nullable: true)]
    private ?string $namaAyah = null;

    #[ORM\Column(name: 'pekerjaan_ayah', type: Types::STRING, length: 100, nullable: true)]
    private ?string $pekerjaanAyah = null;

    #[ORM\Column(name: 'penghasilan_ayah', type: Types::STRING, length: 50, nullable: true)]
    private ?string $penghasilanAyah = null;

    #[ORM\Column(name: 'no_hp_ayah', type: Types::STRING, length: 20, nullable: true)]
    private ?string $noHpAyah = null;

    #[ORM\Column(name: 'nama_ibu', type: Types::STRING, length: 150, nullable: true)]
    private ?string $namaIbu = null;

    #[ORM\Column(name: 'pekerjaan_ibu', type: Types::STRING, length: 100, nullable: true)]
    private ?string $pekerjaanIbu = null;

    #[ORM\Column(name: 'penghasilan_ibu', type: Types::STRING, length: 50, nullable: true)]
    private ?string $penghasilanIbu = null;

    #[ORM\Column(name: 'no_hp_ibu', type: Types::STRING, length: 20, nullable: true)]
    private ?string $noHpIbu = null;

    #[ORM\Column(name: 'nama_wali', type: Types::STRING, length: 150, nullable: true)]
    private ?string $namaWali = null;

    #[ORM\Column(name: 'hubungan_wali', type: Types::STRING, length: 50, nullable: true)]
    private ?string $hubunganWali = null;

    #[ORM\Column(name: 'pekerjaan_wali', type: Types::STRING, length: 100, nullable: true)]
    private ?string $pekerjaanWali = null;

    #[ORM\Column(name: 'no_hp_wali', type: Types::STRING, length: 20, nullable: true)]
    private ?string $noHpWali = null;

    #[ORM\Column(name: 'alamat_orang_tua', type: Types::TEXT, nullable: true)]
    private ?string $alamatOrangTua = null;

    // --- Data Tambahan / Administrasi Sekolah ---

    #[ORM\Column(name: 'anak_ke', type: Types::INTEGER, nullable: true)]
    private ?int $anakKe = null;

    #[ORM\Column(name: 'jumlah_saudara', type: Types::INTEGER, nullable: true)]
    private ?int $jumlahSaudara = null;

    #[ORM\Column(name: 'tanggal_masuk', type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $tanggalMasuk = null;

    #[ORM\Column(name: 'asal_sekolah', type: Types::STRING, length: 150, nullable: true)]
    private ?string $asalSekolah = null;

    #[ORM\Column(name: 'foto_url', type: Types::STRING, length: 255, nullable: true)]
    private ?string $fotoUrl = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $catatan = null;

    #[ORM\Column(name: 'is_active', type: Types::BOOLEAN, options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->jenisKelamin = JenisKelamin::LAKI_LAKI;
        $this->status = StatusSiswa::AKTIF;
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

    public function getNis(): string
    {
        return $this->nis;
    }

    public function setNis(string $nis): self
    {
        $this->nis = trim($nis);
        return $this;
    }

    public function getNisn(): ?string
    {
        return $this->nisn;
    }

    public function setNisn(?string $nisn): self
    {
        $this->nisn = $nisn !== null ? trim($nisn) : null;
        return $this;
    }

    public function getNik(): ?string
    {
        return $this->nik;
    }

    public function setNik(?string $nik): self
    {
        $this->nik = $nik !== null ? trim($nik) : null;
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

    public function getNamaPanggilan(): ?string
    {
        return $this->namaPanggilan;
    }

    public function setNamaPanggilan(?string $namaPanggilan): self
    {
        $this->namaPanggilan = $namaPanggilan !== null ? trim($namaPanggilan) : null;
        return $this;
    }

    public function getNamaArab(): ?string
    {
        return $this->namaArab;
    }

    public function setNamaArab(?string $namaArab): self
    {
        $this->namaArab = $namaArab !== null ? trim($namaArab) : null;
        return $this;
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
        $this->tempatLahir = $tempatLahir !== null ? trim($tempatLahir) : null;
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
        $this->agama = $agama !== null ? trim($agama) : null;
        return $this;
    }

    public function getAlamat(): ?string
    {
        return $this->alamat;
    }

    public function setAlamat(?string $alamat): self
    {
        $this->alamat = $alamat !== null ? trim($alamat) : null;
        return $this;
    }

    public function getRt(): ?string
    {
        return $this->rt;
    }

    public function setRt(?string $rt): self
    {
        $this->rt = $rt !== null ? trim($rt) : null;
        return $this;
    }

    public function getRw(): ?string
    {
        return $this->rw;
    }

    public function setRw(?string $rw): self
    {
        $this->rw = $rw !== null ? trim($rw) : null;
        return $this;
    }

    public function getKelurahan(): ?string
    {
        return $this->kelurahan;
    }

    public function setKelurahan(?string $kelurahan): self
    {
        $this->kelurahan = $kelurahan !== null ? trim($kelurahan) : null;
        return $this;
    }

    public function getKecamatan(): ?string
    {
        return $this->kecamatan;
    }

    public function setKecamatan(?string $kecamatan): self
    {
        $this->kecamatan = $kecamatan !== null ? trim($kecamatan) : null;
        return $this;
    }

    public function getKabupatenKota(): ?string
    {
        return $this->kabupatenKota;
    }

    public function setKabupatenKota(?string $kabupatenKota): self
    {
        $this->kabupatenKota = $kabupatenKota !== null ? trim($kabupatenKota) : null;
        return $this;
    }

    public function getProvinsi(): ?string
    {
        return $this->provinsi;
    }

    public function setProvinsi(?string $provinsi): self
    {
        $this->provinsi = $provinsi !== null ? trim($provinsi) : null;
        return $this;
    }

    public function getKodePos(): ?string
    {
        return $this->kodePos;
    }

    public function setKodePos(?string $kodePos): self
    {
        $this->kodePos = $kodePos !== null ? trim($kodePos) : null;
        return $this;
    }

    public function getNomorHp(): ?string
    {
        return $this->nomorHp;
    }

    public function setNomorHp(?string $nomorHp): self
    {
        $this->nomorHp = $nomorHp !== null ? trim($nomorHp) : null;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email !== null ? trim(strtolower($email)) : null;
        return $this;
    }

    public function getKelas(): ?Kelas
    {
        return $this->kelas;
    }

    public function setKelas(?Kelas $kelas): self
    {
        $this->kelas = $kelas;
        return $this;
    }

    public function getStatus(): StatusSiswa
    {
        return $this->status;
    }

    public function setStatus(StatusSiswa $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getNamaAyah(): ?string
    {
        return $this->namaAyah;
    }

    public function setNamaAyah(?string $namaAyah): self
    {
        $this->namaAyah = $namaAyah !== null ? trim($namaAyah) : null;
        return $this;
    }

    public function getPekerjaanAyah(): ?string
    {
        return $this->pekerjaanAyah;
    }

    public function setPekerjaanAyah(?string $pekerjaanAyah): self
    {
        $this->pekerjaanAyah = $pekerjaanAyah !== null ? trim($pekerjaanAyah) : null;
        return $this;
    }

    public function getPenghasilanAyah(): ?string
    {
        return $this->penghasilanAyah;
    }

    public function setPenghasilanAyah(?string $penghasilanAyah): self
    {
        $this->penghasilanAyah = $penghasilanAyah !== null ? trim($penghasilanAyah) : null;
        return $this;
    }

    public function getNoHpAyah(): ?string
    {
        return $this->noHpAyah;
    }

    public function setNoHpAyah(?string $noHpAyah): self
    {
        $this->noHpAyah = $noHpAyah !== null ? trim($noHpAyah) : null;
        return $this;
    }

    public function getNamaIbu(): ?string
    {
        return $this->namaIbu;
    }

    public function setNamaIbu(?string $namaIbu): self
    {
        $this->namaIbu = $namaIbu !== null ? trim($namaIbu) : null;
        return $this;
    }

    public function getPekerjaanIbu(): ?string
    {
        return $this->pekerjaanIbu;
    }

    public function setPekerjaanIbu(?string $pekerjaanIbu): self
    {
        $this->pekerjaanIbu = $pekerjaanIbu !== null ? trim($pekerjaanIbu) : null;
        return $this;
    }

    public function getPenghasilanIbu(): ?string
    {
        return $this->penghasilanIbu;
    }

    public function setPenghasilanIbu(?string $penghasilanIbu): self
    {
        $this->penghasilanIbu = $penghasilanIbu !== null ? trim($penghasilanIbu) : null;
        return $this;
    }

    public function getNoHpIbu(): ?string
    {
        return $this->noHpIbu;
    }

    public function setNoHpIbu(?string $noHpIbu): self
    {
        $this->noHpIbu = $noHpIbu !== null ? trim($noHpIbu) : null;
        return $this;
    }

    public function getNamaWali(): ?string
    {
        return $this->namaWali;
    }

    public function setNamaWali(?string $namaWali): self
    {
        $this->namaWali = $namaWali !== null ? trim($namaWali) : null;
        return $this;
    }

    public function getHubunganWali(): ?string
    {
        return $this->hubunganWali;
    }

    public function setHubunganWali(?string $hubunganWali): self
    {
        $this->hubunganWali = $hubunganWali !== null ? trim($hubunganWali) : null;
        return $this;
    }

    public function getPekerjaanWali(): ?string
    {
        return $this->pekerjaanWali;
    }

    public function setPekerjaanWali(?string $pekerjaanWali): self
    {
        $this->pekerjaanWali = $pekerjaanWali !== null ? trim($pekerjaanWali) : null;
        return $this;
    }

    public function getNoHpWali(): ?string
    {
        return $this->noHpWali;
    }

    public function setNoHpWali(?string $noHpWali): self
    {
        $this->noHpWali = $noHpWali !== null ? trim($noHpWali) : null;
        return $this;
    }

    public function getAlamatOrangTua(): ?string
    {
        return $this->alamatOrangTua;
    }

    public function setAlamatOrangTua(?string $alamatOrangTua): self
    {
        $this->alamatOrangTua = $alamatOrangTua !== null ? trim($alamatOrangTua) : null;
        return $this;
    }

    public function getAnakKe(): ?int
    {
        return $this->anakKe;
    }

    public function setAnakKe(?int $anakKe): self
    {
        $this->anakKe = $anakKe;
        return $this;
    }

    public function getJumlahSaudara(): ?int
    {
        return $this->jumlahSaudara;
    }

    public function setJumlahSaudara(?int $jumlahSaudara): self
    {
        $this->jumlahSaudara = $jumlahSaudara;
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

    public function getAsalSekolah(): ?string
    {
        return $this->asalSekolah;
    }

    public function setAsalSekolah(?string $asalSekolah): self
    {
        $this->asalSekolah = $asalSekolah !== null ? trim($asalSekolah) : null;
        return $this;
    }

    public function getFotoUrl(): ?string
    {
        return $this->fotoUrl;
    }

    public function setFotoUrl(?string $fotoUrl): self
    {
        $this->fotoUrl = $fotoUrl !== null ? trim($fotoUrl) : null;
        return $this;
    }

    public function getCatatan(): ?string
    {
        return $this->catatan;
    }

    public function setCatatan(?string $catatan): self
    {
        $this->catatan = $catatan !== null ? trim($catatan) : null;
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

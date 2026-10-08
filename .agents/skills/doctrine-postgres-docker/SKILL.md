---
name: doctrine-postgres-docker
description: >-
  Use this skill when managing PostgreSQL with Docker, generating Doctrine entities with
  Symfony Maker, creating and running database migrations, writing repositories, and handling schema validation.
---

# PostgreSQL + Docker + Doctrine ORM + Symfony Maker Runbook

Panduan lengkap untuk pengelolaan database PostgreSQL berbasis Docker, pembuatan entitas via Symfony Maker, migrasi skema, dan best practices Doctrine ORM pada aplikasi ERP Sekolah.

---

## 1. Docker & PostgreSQL Lifecycle

Database berjalan di dalam container Docker menggunakan `compose.yaml` (PostgreSQL 16 Alpine):

```bash
# Menjalankan container database PostgreSQL di background
docker compose up -d database

# Memeriksa status kesehatan container (healthcheck pg_isready)
docker compose ps

# Melihat log database jika terjadi masalah koneksi
docker compose logs -f database

# Mengakses shell psql langsung di dalam container
docker compose exec database psql -U app -d app

# Menghentikan container database
docker compose stop database
```

> [!NOTE]
> Konfigurasi default di [`.env`](file:///home/hor/Documents/Projects/brilize/.env#L29-L37):
> `DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=16&charset=utf8"`

---

## 2. Pembuatan & Modifikasi Entitas (Symfony Maker)

Gunakan `symfony/maker-bundle` untuk scaffolding entitas, repository, dan relasi:

```bash
# 1. Membuat atau menambahkan field ke entitas interaktif
php bin/console make:entity Siswa

# 2. Membuat Enum untuk status / tipe data terbatas
php bin/console make:enum

# 3. Regenerasi getter/setter setelah modifikasi manual pada class Entity
php bin/console make:entity --regenerate App
```

---

## 3. Pola Standar Entitas Doctrine (PHP 8.2+ Attributes)

Setiap entitas harus didefinisikan dengan tipe data presisi, atribut PHP 8, dan indexing yang tepat:

```php
<?php

declare(strict_types=1);

namespace App\Entity\Akademik;

use App\Entity\Akademik\Kelas;
use App\Enum\JenisKelamin;
use App\Enum\StatusSiswa;
use App\Repository\Akademik\SiswaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SiswaRepository::class)]
#[ORM\Table(name: 'akademik_siswa')]
#[ORM\Index(name: 'idx_siswa_nis', columns: ['nis'])]
#[ORM\Index(name: 'idx_siswa_status', columns: ['status'])]
#[ORM\HasLifecycleCallbacks]
class Siswa
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 20, unique: true)]
    private string $nis;

    #[ORM\Column(type: Types::STRING, length: 20, unique: true, nullable: true)]
    private ?string $nisn = null;

    #[ORM\Column(type: Types::STRING, length: 150)]
    private string $namaLengkap;

    #[ORM\Column(type: Types::STRING, length: 1, enumType: JenisKelamin::class)]
    private JenisKelamin $jenisKelamin;

    #[ORM\ManyToOne(targetEntity: Kelas::class, inversedBy: 'siswaList')]
    #[ORM\JoinColumn(name: 'kelas_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Kelas $kelas = null;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: StatusSiswa::class)]
    private StatusSiswa $status = StatusSiswa::AKTIF;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Getter dan Setter...
}
```

### Panduan Tipe Data PostgreSQL untuk Modul ERP:
- **ID Primer**: `Types::BIGINT` dengan `#[ORM\GeneratedValue(strategy: 'IDENTITY')]` (PostgreSQL `BIGSERIAL` / Identity).
- **Nominal Keuangan / SPP**: Selalu gunakan `Types::DECIMAL` dengan `precision: 14, scale: 2`, **JANGAN** gunakan `FLOAT` untuk uang.
- **Teks Panjang / Catatan**: `Types::TEXT` untuk alamat, keterangan BK, atau catatan rapor.
- **Data Konfigurasi / Metadata**: `Types::JSON` untuk nilai dinamis atau rincian komponen biaya.
- **Waktu**: Selalu gunakan `Types::DATETIME_IMMUTABLE` / `\DateTimeImmutable`.

---

## 4. Alur Migrasi Database (Doctrine Migrations)

Setelah memodifikasi atau membuat entitas, ikuti urutan perintah ini:

```bash
# 1. Pastikan database sudah dibuat (jika baru pertama kali setup)
php bin/console doctrine:database:create --if-not-exists

# 2. Validasi mapping Doctrine (memastikan tidak ada error relasi/tipe)
php bin/console doctrine:schema:validate

# 3. Generate file migrasi baru berdasarkan perbedaan Entity vs Database
php bin/console make:migration

# 4. Tinjau file migrasi yang terbuat di `migrations/VersionXXXXXXXXXXXXXX.php`

# 5. Jalankan migrasi ke PostgreSQL
php bin/console doctrine:migrations:migrate --no-interaction

# 6. Cek status migrasi
php bin/console doctrine:migrations:status
```

### Rollback Migrasi (Jika diperlukan):
```bash
# Rollback ke 1 versi sebelumnya
php bin/console doctrine:migrations:migrate prev --no-interaction
```

---

## 5. Query Builder & Optimasi Repository (Hindari N+1 Query)

Gunakan `leftJoin` dan `addSelect` saat mengambil data relasi agar tidak memicu lazy-loading N+1 query:

```php
<?php

declare(strict_types=1);

namespace App\Repository\Akademik;

use App\Entity\Akademik\Siswa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Siswa>
 */
class SiswaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Siswa::class);
    }

    /**
     * Mengambil daftar siswa aktif beserta relasi kelas secara efisien (1 query)
     */
    public function findActiveSiswaWithKelas(?string $keyword = null, ?int $kelasId = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.kelas', 'k')
            ->addSelect('k')
            ->where('s.status = :status')
            ->setParameter('status', 'aktif')
            ->orderBy('s.namaLengkap', 'ASC');

        if ($keyword) {
            $qb->andWhere('LOWER(s.namaLengkap) LIKE :keyword OR s.nis LIKE :keyword')
               ->setParameter('keyword', '%' . strtolower($keyword) . '%');
        }

        if ($kelasId) {
            $qb->andWhere('k.id = :kelasId')
               ->setParameter('kelasId', $kelasId);
        }

        return $qb->getQuery()->getResult();
    }
}
```

---

## 6. Checklist & Troubleshooting

| Masalah | Solusi |
| :--- | :--- |
| **`Connection refused` / `SQLSTATE[08006]`** | Pastikan Docker container database aktif: `docker compose up -d database`. |
| **Schema Validate `[FAIL]`** | Jalankan `php bin/console make:migration` lalu `php bin/console doctrine:migrations:migrate`. |
| **Foreign Key constraint error saat delete** | Pastikan `onDelete` didefinisikan pada `#[ORM\JoinColumn]` (`SET NULL` atau `CASCADE`). |
| **Karakter & Encoding** | Gunakan `charset=utf8` pada `DATABASE_URL` untuk support UTF-8 penuh di PostgreSQL. |

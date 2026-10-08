---
name: symfony-inertia-vue
description: >-
  Use this skill when developing, refactoring, or integrating full-stack features with
  Symfony 7.4, Nytodev InertiaBundle, and Vue 3 + TypeScript in this school ERP project.
  Enforces a strict 3-tier architecture: Controller -> Service -> Repository.
---

# Symfony 7.4 + Inertia.js + Vue 3 (TypeScript) Fullstack Workflow

Panduan standar untuk arsitektur fullstack end-to-end pada aplikasi ERP Sekolah ini menggunakan pola **3 Layer Backend (Controller -> Service -> Repository)**, **Inertia.js** (via `nytodev/inertia-bundle`), **Vue 3** (`<script setup lang="ts">`), dan **TypeScript**.

---

## 1. Arsitektur 3 Layer & Alur Data

```
+-------------------------------------------------------------------------------+
| Browser / Client                                                              |
| Vue 3 Pages (`resources/js/Pages/**/*.vue`) + Inertia Form/Link / TypeScript   |
+---------------------------------------+---------------------------------------+
                                        | HTTP Request (Inertia Visit / Form Post)
                                        v
+-------------------------------------------------------------------------------+
| LAYER 1: CONTROLLER (`src/Controller/`)                                       |
| - Menerima HTTP Request, routing (`#[Route]`), mapping ke DTO.                |
| - Thin Controller: Mendelegasikan seluruh pemrosesan bisnis ke Service.       |
| - Mengembalikan respon Inertia (`$inertia->render()`) atau Redirect.          |
+---------------------------------------+---------------------------------------+
                                        | DTO / Parameter Primitif
                                        v
+-------------------------------------------------------------------------------+
| LAYER 2: SERVICE (`src/Service/`)                                             |
| - Business Logic & Orchestration (Aturan bisnis, validasi, kalkulasi).        |
| - Memanggil Repository untuk I/O database dan mutasi data.                   |
| - Hashing password, dispatch event/subscriber, error handling.               |
| - Mengembalikan Entity atau ResponseDTO ke Controller.                        |
+---------------------------------------+---------------------------------------+
                                        | Query / Entity
                                        v
+-------------------------------------------------------------------------------+
| LAYER 3: REPOSITORY (`src/Repository/`)                                       |
| - Data Access Layer (Doctrine ORM, QueryBuilder, DQL).                        |
| - Query filtering, pencarian, sorting, dan eager loading join (cegah N+1).    |
| - Operasi persistensi: `save($entity, $flush)` & `remove($entity, $flush)`.   |
+-------------------------------------------------------------------------------+
```

---

## 2. Aturan & Batasan Setiap Layer

| Layer | Lokasi | Boleh Dilakukan (DO) | Dilarang (DON'T) |
| :--- | :--- | :--- | :--- |
| **Controller** | `src/Controller/` | Routing (`#[Route]`), membaca HTTP request/query/session, memanggil DTO parser, memanggil Service, merender `$inertia->render()` atau `$this->redirectToRoute()`, set Flash message. | ❌ Query database langsung via `createQueryBuilder` atau `EntityManager`, ❌ Menulis business logic/kalkulasi rumit. |
| **Service** | `src/Service/` | Validasi aturan bisnis, koordinasi transaksi (`wrapInTransaction`), hashing/enkripsi, pemanggilan Repository, transformasi Entity -> ResponseDTO. | ❌ Menerima objek `Request` atau `Response` HTTP langsung (gunakan DTO / typed parameter), ❌ Render template / Inertia. |
| **Repository** | `src/Repository/` | QueryBuilder, DQL, query filtering/pagination, eager load relations (`leftJoin`, `addSelect`), persist/remove Doctrine entities. | ❌ Logika bisnis non-query, ❌ Mengakses session/user context secara langsung, ❌ Dependency ke controller/UI. |
| **DTO** | `src/DTO/` | Enkapsulasi input form/request, sanitasi, type casting, dan serialisasi data output yang aman untuk Inertia props. | ❌ Eksekusi query database di dalam DTO. |

---

## 3. Implementasi Kode Lengkap (Contoh Kasus: Modul Siswa)

### A. DTO Layer (`src/DTO/Akademik/CreateSiswaDTO.php`)
```php
<?php

declare(strict_types=1);

namespace App\DTO\Akademik;

use Symfony\Component\HttpFoundation\Request;

final readonly class CreateSiswaDTO
{
    public function __construct(
        public string $nis,
        public ?string $nisn,
        public string $namaLengkap,
        public string $jenisKelamin, // 'L' | 'P'
        public ?int $kelasId = null,
        public ?string $alamat = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $payload = $request->getPayload();

        return new self(
            nis: trim($payload->getString('nis', (string) $request->request->get('nis', ''))),
            nisn: $payload->has('nisn') && $payload->getString('nisn') !== '' ? trim($payload->getString('nisn')) : null,
            namaLengkap: trim($payload->getString('nama_lengkap', (string) $request->request->get('nama_lengkap', ''))),
            jenisKelamin: $payload->getString('jenis_kelamin', (string) $request->request->get('jenis_kelamin', 'L')),
            kelasId: $payload->has('kelas_id') && $payload->get('kelas_id') ? (int) $payload->get('kelas_id') : null,
            alamat: $payload->has('alamat') && $payload->getString('alamat') !== '' ? trim($payload->getString('alamat')) : null,
        );
    }
}
```

### B. Layer 3: Repository (`src/Repository/Akademik/SiswaRepository.php`)
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

    public function save(Siswa $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Siswa $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Siswa[]
     */
    public function searchSiswa(?string $query = null, ?int $kelasId = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.kelas', 'k')
            ->addSelect('k')
            ->orderBy('s.namaLengkap', 'ASC');

        if ($query) {
            $qb->andWhere('LOWER(s.namaLengkap) LIKE :q OR s.nis LIKE :q OR s.nisn LIKE :q')
               ->setParameter('q', '%' . strtolower(trim($query)) . '%');
        }

        if ($kelasId) {
            $qb->andWhere('k.id = :kelasId')
               ->setParameter('kelasId', $kelasId);
        }

        return $qb->getQuery()->getResult();
    }
}
```

### C. Layer 2: Service (`src/Service/Akademik/SiswaService.php`)
```php
<?php

declare(strict_types=1);

namespace App\Service\Akademik;

use App\DTO\Akademik\CreateSiswaDTO;
use App\DTO\Akademik\SiswaResponseDTO;
use App\Entity\Akademik\Siswa;
use App\Repository\Akademik\KelasRepository;
use App\Repository\Akademik\SiswaRepository;
use InvalidArgumentException;

final readonly class SiswaService
{
    public function __construct(
        private SiswaRepository $siswaRepository,
        private KelasRepository $kelasRepository,
    ) {}

    /**
     * @return list<SiswaResponseDTO>
     */
    public function getDaftarSiswa(?string $query = null, ?int $kelasId = null): array
    {
        $siswaList = $this->siswaRepository->searchSiswa($query, $kelasId);

        return array_map(fn(Siswa $s) => SiswaResponseDTO::fromEntity($s), $siswaList);
    }

    public function tambahSiswa(CreateSiswaDTO $dto): SiswaResponseDTO
    {
        if ($this->siswaRepository->findOneBy(['nis' => $dto->nis])) {
            throw new InvalidArgumentException('NIS sudah terdaftar untuk siswa lain.');
        }

        $siswa = new Siswa();
        $siswa->setNis($dto->nis);
        $siswa->setNisn($dto->nisn);
        $siswa->setNamaLengkap($dto->namaLengkap);
        $siswa->setJenisKelamin($dto->jenisKelamin);
        $siswa->setAlamat($dto->alamat);

        if ($dto->kelasId !== null) {
            $kelas = $this->kelasRepository->find($dto->kelasId);
            $siswa->setKelas($kelas);
        }

        $this->siswaRepository->save($siswa, true);

        return SiswaResponseDTO::fromEntity($siswa);
    }

    public function hapusSiswa(int $id): void
    {
        $siswa = $this->siswaRepository->find($id);
        if ($siswa !== null) {
            $this->siswaRepository->remove($siswa, true);
        }
    }
}
```

### D. Layer 1: Controller (`src/Controller/Akademik/SiswaController.php`)
```php
<?php

declare(strict_types=1);

namespace App\Controller\Akademik;

use App\DTO\Akademik\CreateSiswaDTO;
use App\Service\Akademik\SiswaService;
use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use InvalidArgumentException;

#[Route('/akademik/siswa', name: 'akademik_siswa_')]
final class SiswaController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, SiswaService $siswaService, Inertia $inertia): Response
    {
        $query = $request->query->get('q');
        $kelasId = $request->query->get('kelas_id') ? (int) $request->query->get('kelas_id') : null;

        $siswaList = $siswaService->getDaftarSiswa($query ? (string) $query : null, $kelasId);

        return $inertia->render('Akademik/Siswa/Index', [
            'siswaList' => array_map(fn($s) => $s->toArray(), $siswaList),
            'filters' => [
                'q' => $query,
                'kelas_id' => $kelasId,
            ],
        ]);
    }

    #[Route('', name: 'store', methods: ['POST'])]
    public function store(Request $request, SiswaService $siswaService): Response
    {
        $dto = CreateSiswaDTO::fromRequest($request);

        try {
            $siswaService->tambahSiswa($dto);
            $this->addFlash('success', 'Data siswa berhasil ditambahkan.');
        } catch (InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('akademik_siswa_index');
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, SiswaService $siswaService): Response
    {
        $siswaService->hapusSiswa($id);
        $this->addFlash('success', 'Data siswa berhasil dihapus.');

        return $this->redirectToRoute('akademik_siswa_index');
    }
}
```

---

## 4. Frontend Vue 3 Component (`resources/js/Pages/Akademik/Siswa/Index.vue`)

```vue
<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

interface SiswaItem {
  id: number
  nis: string
  nisn?: string
  namaLengkap: string
  jenisKelamin: 'L' | 'P'
  kelas?: string
  status: string
  statusBadge: string
}

interface Props {
  siswaList: SiswaItem[]
  filters: {
    q?: string
    kelas_id?: number
  }
}

const props = defineProps<Props>()
const search = ref(props.filters.q ?? '')

watch(search, (val) => {
  router.get('/akademik/siswa', { q: val }, { preserveState: true, replace: true })
})

function hapusSiswa(id: number, nama: string) {
  if (confirm(`Apakah Anda yakin ingin menghapus data siswa "${nama}"?`)) {
    router.delete(`/akademik/siswa/${id}`, { preserveScroll: true })
  }
}
</script>

<template>
  <div class="space-y-6">
    <Head title="Data Siswa - Portal Akademik" />

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">Data Siswa</h1>
        <p class="text-sm opacity-70">Manajemen direktori siswa aktif dan arsip akademik.</p>
      </div>
      <div class="flex items-center gap-3">
        <input v-model="search" type="text" placeholder="Cari NIS / Nama..." class="input input-bordered input-sm w-48 sm:w-64" />
        <Link href="/akademik/siswa/tambah" class="btn btn-primary btn-sm">+ Tambah Siswa</Link>
      </div>
    </div>

    <div class="card bg-base-100 shadow border border-base-200">
      <div class="overflow-x-auto">
        <table class="table table-zebra w-full">
          <thead>
            <tr>
              <th>NIS / NISN</th>
              <th>Nama Lengkap</th>
              <th>L/P</th>
              <th>Kelas</th>
              <th>Status</th>
              <th class="text-right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in props.siswaList" :key="item.id" class="hover">
              <td>
                <div class="font-bold">{{ item.nis }}</div>
                <div class="text-xs opacity-60">{{ item.nisn ?? '-' }}</div>
              </td>
              <td class="font-medium">{{ item.namaLengkap }}</td>
              <td>
                <span class="badge badge-sm" :class="item.jenisKelamin === 'L' ? 'badge-info' : 'badge-secondary'">
                  {{ item.jenisKelamin }}
                </span>
              </td>
              <td>{{ item.kelas ?? '-' }}</td>
              <td>
                <span class="badge badge-sm" :class="item.statusBadge">
                  {{ item.status }}
                </span>
              </td>
              <td class="text-right space-x-2">
                <button @click="hapusSiswa(item.id, item.namaLengkap)" class="btn btn-ghost btn-xs text-error">
                  Hapus
                </button>
              </td>
            </tr>
            <tr v-if="props.siswaList.length === 0">
              <td colspan="6" class="text-center py-8 opacity-60">
                Belum ada data siswa yang sesuai.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
```

---

## 5. Checklist Validasi & Best Practices

1. **Strict 3-Tier Separation**:
   - Controller *hanya* berurusan dengan HTTP & Inertia view dispatching.
   - Service *hanya* berurusan dengan business logic, validasi, dan orkestrasinya.
   - Repository *hanya* berurusan dengan Doctrine query & persistensi.
2. **DTO for Data Transport**: Selalu gunakan DTO untuk parsing request dan response agar payload type-safe dan aman dari kebocoran data sensitif.
3. **Frontend Package Management**: Selalu gunakan `bun` (`bun run dev`, `bun run build`, `bun add <pkg>`).

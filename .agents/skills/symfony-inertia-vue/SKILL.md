---
name: symfony-inertia-vue
description: >-
  Use this skill when developing, refactoring, or integrating full-stack features with
  Symfony 7.4, Nytodev InertiaBundle, and Vue 3 + TypeScript in this school ERP project.
---

# Symfony 7.4 + Inertia.js + Vue 3 (TypeScript) Fullstack Workflow

Panduan standar untuk mengembangkan fitur fullstack end-to-end pada aplikasi ERP Sekolah ini menggunakan **Symfony 7.4**, **Inertia.js** (via `nytodev/inertia-bundle`), **Vue 3** (Composition API `<script setup lang="ts">`), dan **TypeScript**.

---

## 1. Arsitektur & Pola Alur Data

```
+-------------------------------------------------------------+
| Browser / Client                                            |
| Vue 3 Pages (`resources/js/Pages/**/*.vue`) + Inertia Link  |
+------------------------------+------------------------------+
                               | Request (Inertia Visit/Form)
                               v
+-------------------------------------------------------------+
| Symfony 7.4 Backend                                         |
| Controller -> Service -> Entity/Repository                  |
| Return: `$inertia->render('Domain/PageName', $props)`       |
+-------------------------------------------------------------+
```

- **Frontend Pages Location**: `resources/js/Pages/`
- **Entry Point**: `assets/app.ts`
- **Vite Config**: `vite.config.ts` (menggunakan `vite-plugin-symfony` dan `@tailwindcss/vite`)
- **Package Manager**: Selalu gunakan `bun` (bukan `npm`/`yarn`/`pnpm`).

---

## 2. Standar Symfony Controller (PHP 8.2+ / Symfony 7.4)

Gunakan PHP Attributes untuk routing dan inject `Nytodev\InertiaBundle\Service\Inertia` untuk render page:

```php
<?php

declare(strict_types=1);

namespace App\Controller\Akademik;

use App\Entity\Siswa;
use App\Repository\SiswaRepository;
use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/akademik/siswa', name: 'akademik_siswa_')]
final class SiswaController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(SiswaRepository $siswaRepo, Inertia $inertia): Response
    {
        $siswaList = $siswaRepo->findBy([], ['namaLengkap' => 'ASC']);

        return $inertia->render('Akademik/Siswa/Index', [
            'siswaList' => array_map(fn(Siswa $s) => [
                'id' => $s->getId(),
                'nis' => $s->getNis(),
                'nisn' => $s->getNisn(),
                'namaLengkap' => $s->getNamaLengkap(),
                'jenisKelamin' => $s->getJenisKelamin(),
                'kelas' => $s->getKelas()?->getNamaKelas(),
                'status' => $s->getStatus(),
            ], $siswaList),
        ]);
    }

    #[Route('/tambah', name: 'create', methods: ['GET'])]
    public function create(Inertia $inertia): Response
    {
        return $inertia->render('Akademik/Siswa/Form', [
            'mode' => 'create',
            'siswa' => null,
        ]);
    }

    #[Route('', name: 'store', methods: ['POST'])]
    public function store(Request $request, Inertia $inertia): Response
    {
        // Validasi & Simpan Data
        // Redirect setelah mutasi:
        $this->addFlash('success', 'Data siswa berhasil ditambahkan');
        return $this->redirectToRoute('akademik_siswa_index');
    }
}
```

---

## 3. Standar Vue 3 + TypeScript Component

Setiap halaman Vue harus menggunakan `<script setup lang="ts">`, tipe data yang jelas, serta komponen Inertia (`Link`, `Head`, `useForm`, `router`):

### Contoh: `resources/js/Pages/Akademik/Siswa/Index.vue`

```vue
<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

interface SiswaItem {
  id: number
  nis: string
  nisn: string
  namaLengkap: string
  jenisKelamin: 'L' | 'P'
  kelas?: string
  status: 'aktif' | 'lulus' | 'mutasi' | 'nonaktif'
}

interface Props {
  siswaList: SiswaItem[]
}

const props = defineProps<Props>()

function hapusSiswa(id: number, nama: string) {
  if (confirm(`Apakah Anda yakin ingin menghapus data siswa "${nama}"?`)) {
    router.delete(`/akademik/siswa/${id}`, {
      preserveScroll: true,
    })
  }
}
</script>

<template>
  <AppLayout>
    <Head title="Data Siswa - ERP Sekolah" />

    <div class="space-y-6">
      <!-- Header Section -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Data Siswa</h1>
          <p class="text-sm opacity-70">Manajemen direktori siswa aktif dan arsip akademik.</p>
        </div>
        <div>
          <Link href="/akademik/siswa/tambah" class="btn btn-primary btn-sm md:btn-md shadow-sm">
            + Tambah Siswa
          </Link>
        </div>
      </div>

      <!-- Table Section -->
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
                  <div class="text-xs opacity-60">{{ item.nisn }}</div>
                </td>
                <td class="font-medium">{{ item.namaLengkap }}</td>
                <td>
                  <span class="badge badge-sm" :class="item.jenisKelamin === 'L' ? 'badge-info' : 'badge-secondary'">
                    {{ item.jenisKelamin }}
                  </span>
                </td>
                <td>{{ item.kelas ?? '-' }}</td>
                <td>
                  <span
                    class="badge badge-sm"
                    :class="{
                      'badge-success': item.status === 'aktif',
                      'badge-neutral': item.status === 'lulus',
                      'badge-warning': item.status === 'mutasi',
                      'badge-error': item.status === 'nonaktif',
                    }"
                  >
                    {{ item.status }}
                  </span>
                </td>
                <td class="text-right space-x-2">
                  <Link :href="`/akademik/siswa/${item.id}/edit`" class="btn btn-ghost btn-xs">
                    Edit
                  </Link>
                  <button @click="hapusSiswa(item.id, item.namaLengkap)" class="btn btn-ghost btn-xs text-error">
                    Hapus
                  </button>
                </td>
              </tr>
              <tr v-if="props.siswaList.length === 0">
                <td colspan="6" class="text-center py-8 opacity-60">
                  Belum ada data siswa.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
```

---

## 4. Form Handling dengan `useForm`

Gunakan `useForm` dari `@inertiajs/vue3` untuk penanganan form terintegrasi:

```vue
<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  nis: '',
  nisn: '',
  namaLengkap: '',
  jenisKelamin: 'L',
  kelasId: '',
})

function submit() {
  form.post('/akademik/siswa', {
    onSuccess: () => {
      form.reset()
    },
  })
}
</script>

<template>
  <form @submit.prevent="submit" class="space-y-4">
    <div class="form-control">
      <label class="label"><span class="label-text">NIS</span></label>
      <input v-model="form.nis" type="text" class="input input-bordered" :class="{ 'input-error': form.errors.nis }" />
      <span v-if="form.errors.nis" class="text-error text-xs mt-1">{{ form.errors.nis }}</span>
    </div>

    <button type="submit" class="btn btn-primary" :disabled="form.processing">
      <span v-if="form.processing" class="loading loading-spinner"></span>
      Simpan Data
    </button>
  </form>
</template>
```

---

## 5. Checklist Validasi & Best Practices

1. **Resolusi Page**: Pastikan file `.vue` berada di `resources/js/Pages/` sesuai resolver di `assets/app.ts`.
2. **Type Safety**: Definisikan interface TypeScript untuk setiap prop dan model data.
3. **SPA Navigation**: Selalu gunakan `<Link>` dari `@inertiajs/vue3` untuk link internal, hindari tag `<a>` biasa.
4. **Command Execution**: Jalankan `bun run dev` untuk development server Vite dan `bun run vue-tsc --noEmit` untuk type check.

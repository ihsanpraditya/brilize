---
name: daisyui-tailwind-ui
description: >-
  Use this skill when building, styling, or refactoring UI components, dashboards,
  forms, tables, and layouts using Tailwind CSS 4.3 and DaisyUI 5.7 in Vue 3.
---

# Tailwind CSS 4.3 + DaisyUI 5.7 UI/UX Styling Guide

Panduan standar pembuatan antarmuka (UI), komponen modular, dan tema ERP Sekolah menggunakan **Tailwind CSS v4.3** dan **DaisyUI v5.7**.

---

## 1. Setup & Konfigurasi CSS

Tailwind CSS v4 menggunakan konfigurasi berbasis CSS murni di `assets/app.css`:

```css
@import "tailwindcss";
@plugin "daisyui";

@custom-variant dark (&:where(.dark, .dark *));

@theme {
  --color-primary: var(--color-primary);
  --color-secondary: var(--color-secondary);
  --color-muted: var(--color-muted);
  --color-accent: var(--color-accent);
}
```

---

## 2. Layout Standar ERP Sekolah (Sidebar + Navbar)

Struktur layout admin ERP yang responsif menggunakan DaisyUI `drawer`:

```vue
<!-- resources/js/Layouts/AppLayout.vue -->
<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const user = computed(() => page.props.auth?.user)
const flash = computed(() => page.props.flash)
</script>

<template>
  <div class="drawer lg:drawer-open min-h-screen bg-base-200">
    <input id="erp-drawer" type="checkbox" class="drawer-toggle" />

    <!-- Konten Utama -->
    <div class="drawer-content flex flex-col">
      <!-- Navbar Atas -->
      <header class="navbar bg-base-100 border-b border-base-300 sticky top-0 z-30 px-4">
        <div class="flex-none lg:hidden">
          <label for="erp-drawer" aria-label="open sidebar" class="btn btn-square btn-ghost">
            <svg class="inline-block h-6 w-6 stroke-current" fill="none" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </label>
        </div>

        <div class="flex-1">
          <span class="font-semibold text-lg">Sistem ERP Sekolah</span>
        </div>

        <!-- Profil & Notifikasi -->
        <div class="flex-none gap-2">
          <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
              <div class="w-9 rounded-full bg-primary text-primary-content flex items-center justify-center font-bold">
                {{ user?.name?.charAt(0) ?? 'A' }}
              </div>
            </div>
            <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-1 p-2 shadow bg-base-100 rounded-box w-52 border border-base-200">
              <li><Link href="/profile">Profil Saya</Link></li>
              <li><Link href="/pengaturan">Pengaturan</Link></li>
              <div class="divider my-1"></div>
              <li><Link href="/logout" method="post" as="button" class="text-error">Keluar</Link></li>
            </ul>
          </div>
        </div>
      </header>

      <!-- Alert / Flash Notification -->
      <div v-if="flash?.success" class="p-4 pb-0">
        <div role="alert" class="alert alert-success text-success-content shadow-sm">
          <span>{{ flash.success }}</span>
        </div>
      </div>
      <div v-if="flash?.error" class="p-4 pb-0">
        <div role="alert" class="alert alert-error text-error-content shadow-sm">
          <span>{{ flash.error }}</span>
        </div>
      </div>

      <!-- Main Slot -->
      <main class="flex-1 p-4 md:p-6">
        <slot />
      </main>
    </div>

    <!-- Sidebar Menu -->
    <aside class="drawer-side z-40">
      <label for="erp-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
      <div class="menu p-4 w-72 min-h-full bg-base-100 text-base-content border-r border-base-300">
        <div class="px-2 py-4 mb-4 flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center text-primary-content font-extrabold text-xl shadow">
            ES
          </div>
          <div>
            <div class="font-bold leading-none">Brilize ERP</div>
            <div class="text-xs opacity-60">Portal Akademik</div>
          </div>
        </div>

        <ul class="space-y-1">
          <li><Link href="/" class="active:bg-primary font-medium">Dashboard</Link></li>
          <li class="menu-title mt-4">AKADEMIK</li>
          <li><Link href="/akademik/siswa">Data Siswa</Link></li>
          <li><Link href="/akademik/guru">Data Guru / PTK</Link></li>
          <li><Link href="/akademik/kelas">Kelas & Rombel</Link></li>
          <li><Link href="/akademik/jadwal">Jadwal Pelajaran</Link></li>
          <li><Link href="/akademik/presensi">Presensi Harian</Link></li>
          <li><Link href="/akademik/nilai">Penilaian & Rapor</Link></li>

          <li class="menu-title mt-4">KEUANGAN & SPP</li>
          <li><Link href="/keuangan/tagihan">Tagihan SPP</Link></li>
          <li><Link href="/keuangan/transaksi">Kasir & Pembayaran</Link></li>
          <li><Link href="/keuangan/laporan">Laporan Keuangan</Link></li>

          <li class="menu-title mt-4">PENGATURAN</li>
          <li><Link href="/pengaturan/tahun-ajaran">Tahun Ajaran</Link></li>
          <li><Link href="/pengaturan/users">Manajemen User</Link></li>
        </ul>
      </div>
    </aside>
  </div>
</template>
```

---

## 3. Komponen-Komponen Penting ERP

### A. Kartu Metrik / Statistik (`Stats`)
```vue
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
  <div class="stat bg-base-100 rounded-box shadow border border-base-200">
    <div class="stat-title text-xs font-semibold uppercase">Total Siswa</div>
    <div class="stat-value text-primary text-3xl">1,240</div>
    <div class="stat-desc">Siswa aktif tahun 2026/2027</div>
  </div>
  <div class="stat bg-base-100 rounded-box shadow border border-base-200">
    <div class="stat-title text-xs font-semibold uppercase">Total Guru & PTK</div>
    <div class="stat-value text-secondary text-3xl">78</div>
    <div class="stat-desc">Guru & tenaga kependidikan</div>
  </div>
  <div class="stat bg-base-100 rounded-box shadow border border-base-200">
    <div class="stat-title text-xs font-semibold uppercase">Kehadiran Hari Ini</div>
    <div class="stat-value text-success text-3xl">97.4%</div>
    <div class="stat-desc">1,208 hadir dari 1,240</div>
  </div>
  <div class="stat bg-base-100 rounded-box shadow border border-base-200">
    <div class="stat-title text-xs font-semibold uppercase">SPP Bulan Ini</div>
    <div class="stat-value text-accent text-3xl">84%</div>
    <div class="stat-desc">1,041 siswa telah lunas</div>
  </div>
</div>
```

### B. Form Controls & Validation
```vue
<div class="form-control w-full">
  <label class="label">
    <span class="label-text font-medium">Nomor Induk Siswa (NIS)</span>
    <span class="label-text-alt text-error">*Wajib</span>
  </label>
  <input
    v-model="form.nis"
    type="text"
    placeholder="Contoh: 20261001"
    class="input input-bordered w-full"
    :class="{ 'input-error': form.errors.nis }"
  />
  <div v-if="form.errors.nis" class="label">
    <span class="label-text-alt text-error">{{ form.errors.nis }}</span>
  </div>
</div>
```

### C. Modal Konfirmasi Aksi
```vue
<dialog id="delete_modal" class="modal modal-bottom sm:modal-middle">
  <div class="modal-box">
    <h3 class="font-bold text-lg text-error">Konfirmasi Hapus</h3>
    <p class="py-4">Apakah Anda yakin ingin menghapus data ini? Aksi ini tidak dapat dibatalkan.</p>
    <div class="modal-action">
      <form method="dialog">
        <button class="btn btn-ghost mr-2">Batal</button>
        <button class="btn btn-error" @click="confirmDelete">Hapus</button>
      </form>
    </div>
  </div>
</dialog>
```

---

## 4. Pola Status Badges untuk ERP
- **Status Aktif / Lunas**: `<span class="badge badge-success badge-sm">Aktif</span>`
- **Status Tertunda / Belum Bayar**: `<span class="badge badge-warning badge-sm">Belum Lunas</span>`
- **Status Dibatalkan / Keluar**: `<span class="badge badge-error badge-sm">Nonaktif</span>`
- **Status Netral / Selesai / Lulus**: `<span class="badge badge-neutral badge-sm">Lulus</span>`
- **Informasi / Izin**: `<span class="badge badge-info badge-sm">Izin</span>`

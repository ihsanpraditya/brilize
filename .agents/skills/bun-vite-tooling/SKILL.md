---
name: bun-vite-tooling
description: >-
  Use this skill when running shell commands, managing JavaScript/TypeScript packages,
  building assets, running dev servers, or checking types strictly using Bun and Vite.
---

# Bun + Vite + Symfony Tooling Runbook

Panduan eksekusi perintah terminal, build pipeline, dan manajemen paket khusus untuk proyek ini.

> [!IMPORTANT]
> **ATURAN WAJIB: SELALU GUNAKAN `bun`**, JANGAN PERNAH menggunakan `npm`, `yarn`, atau `pnpm`.
> Proyek ini menggunakan `bun.lock` dan runtime Bun.

---

## 1. Perintah Frontend dengan `bun`

### A. Development & Build
| Perintah | Deskripsi |
| :--- | :--- |
| `bun run dev` | Menjalankan Vite development server (HMR aktif). |
| `bun run build` | Menjalankan build bundle production ke `public/build/`. |
| `bun run vue-tsc --noEmit` | Menjalankan pemeriksaan tipe TypeScript pada file Vue & TS. |

### B. Manajemen Paket (Dependencies)
| Operasi | Perintah |
| :--- | :--- |
| **Instal dependency baru** | `bun add <nama-package>` |
| **Instal dev dependency baru** | `bun add -d <nama-package>` |
| **Hapus dependency** | `bun remove <nama-package>` |
| **Instal seluruh dependensi dari lockfile** | `bun install` |

*Contoh menambahkan icon pack atau library utility:*
```bash
bun add lucide-vue-next
bun add -d @types/lodash-es
```

---

## 2. Perintah Backend dengan Symfony CLI / PHP

| Operasi | Perintah |
| :--- | :--- |
| **Clear Cache Symfony** | `php bin/console cache:clear` |
| **Lihat Daftar Route** | `php bin/console debug:router` |
| **Generate Controller** | `php bin/console make:controller <NamaController>` |
| **Generate Entity (Doctrine)** | `php bin/console make:entity <NamaEntity>` |
| **Buat Migrasi DB** | `php bin/console make:migration` |
| **Jalankan Migrasi DB** | `php bin/console doctrine:migrations:migrate` |
| **Install Composer Packages** | `composer require <vendor/package>` |

---

## 3. Integrasi Vite + Symfony (`pentatrion/vite-bundle`)

1. File manifest hasil build akan disimpan di `public/build/manifest.json`.
2. Halaman root HTML dirender melalui `templates/base.html.twig` yang memuat tag Vite:
   ```twig
   {{ vite_entry_script_tags('app') }}
   {{ vite_entry_link_tags('app') }}
   ```
3. Saat mode development, pastikan server Symfony dan `bun run dev` berjalan bersamaan agar Hot Module Reloading (HMR) berfungsi lancar.

---

## 4. Troubleshooting & Checklist Masalah Umum

1. **Error `npm: command not found` / Salah Pakai Tool**:
   - Pastikan selalu mengetik `bun` bukan `npm`.
2. **Komponen Vue tidak ter-resolve di Inertia**:
   - Periksa `assets/app.ts` (`import.meta.glob('../resources/js/Pages/**/*.vue')`).
   - Pastikan nama path di `$inertia->render('NamaFolder/NamaFile')` cocok persis (case-sensitive) dengan file di `resources/js/Pages/NamaFolder/NamaFile.vue`.
3. **Tailwind / DaisyUI Class tidak muncul**:
   - Pastikan class ditulis dengan format valid Tailwind v4 & DaisyUI v5.
   - Cek apakah `@import "tailwindcss";` dan `@plugin "daisyui";` sudah ada di `assets/app.css`.
4. **Error TypeScript pada komponen `.vue`**:
   - Jalankan `bun run vue-tsc --noEmit` untuk melihat detail line error sebelum komit/build.

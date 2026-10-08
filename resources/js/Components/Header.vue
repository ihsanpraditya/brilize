<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const user = computed(() => (page.props.auth as any)?.user)
</script>

<template>
  <header class="navbar bg-base-100 border-b border-base-300 sticky top-0 z-30 px-4 shadow-sm">
    
    <!-- Toggle button untuk Mobile Sidebar -->
    <div class="flex-none lg:hidden">
      <label for="erp-drawer" aria-label="Buka Sidebar" class="btn btn-square btn-ghost">
        <svg class="h-6 w-6 stroke-current" fill="none" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </label>
    </div>

    <!-- Breadcrumb / Info Tahun Ajaran -->
    <div class="flex-1 px-2">
      <div class="flex items-center gap-3">
        <h2 class="font-bold text-base md:text-lg tracking-tight hidden sm:inline-block">
          Portal ERP Sekolah
        </h2>
        <div class="badge badge-primary badge-outline badge-sm hidden md:inline-flex font-medium">
          T.A 2026/2027 • Ganjil
        </div>
      </div>
    </div>

    <!-- Right Action Items (Notifikasi & Profil) -->
    <div class="flex-none items-center gap-3">
      
      <!-- Notifikasi Dropdown -->
      <div class="dropdown dropdown-end">
        <button tabindex="0" class="btn btn-ghost btn-circle btn-sm" aria-label="Notifikasi">
          <div class="indicator">
            <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <span class="badge badge-xs badge-primary indicator-item"></span>
          </div>
        </button>
        <ul tabindex="0" class="dropdown-content menu p-3 shadow-xl bg-base-100 rounded-box w-72 border border-base-200 mt-3 text-xs space-y-2 z-50">
          <li class="font-bold text-sm px-2">Notifikasi Terbaru</li>
          <li class="p-2 rounded bg-base-200">
            <span class="font-medium">Jadwal PTS telah diperbarui oleh Kurikulum.</span>
            <span class="text-xs opacity-50">10 menit yang lalu</span>
          </li>
          <li class="p-2 rounded bg-base-200">
            <span class="font-medium">12 transaksi SPP baru masuk verifikasi kasir.</span>
            <span class="text-xs opacity-50">1 jam yang lalu</span>
          </li>
        </ul>
      </div>

      <!-- User Profile Dropdown -->
      <div class="dropdown dropdown-end">
        <div tabindex="0" role="button" class="btn btn-ghost flex items-center gap-2 pl-2 pr-3 normal-case">
          <div class="avatar placeholder">
            <div class="bg-primary text-primary-content rounded-full w-8 h-8 font-bold text-sm shadow-sm">
              {{ user?.name ? user.name.charAt(0).toUpperCase() : 'U' }}
            </div>
          </div>
          <div class="text-left hidden md:block leading-tight">
            <div class="font-semibold text-sm">{{ user?.name ?? 'Pengguna' }}</div>
            <div class="text-xs opacity-60">{{ user?.identifier ?? 'Aktif' }}</div>
          </div>
          <svg class="w-4 h-4 opacity-50 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </div>
        
        <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-50 p-2 shadow-xl bg-base-100 rounded-2xl w-56 border border-base-200">
          <li class="menu-title px-4 py-2 border-b border-base-200 mb-1">
            <span class="font-bold text-base-content">{{ user?.name ?? 'Akun Saya' }}</span>
            <span class="text-xs font-normal opacity-60">{{ user?.roles?.[0] ?? 'Role Pengguna' }}</span>
          </li>
          <li>
            <Link href="/users">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
              Manajemen Pengguna
            </Link>
          </li>
          <div class="divider my-1"></div>
          <li>
            <Link href="/logout" method="post" as="button" class="text-error font-medium hover:bg-error/10">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
              </svg>
              Keluar (Logout)
            </Link>
          </li>
        </ul>
      </div>

    </div>
  </header>
</template>

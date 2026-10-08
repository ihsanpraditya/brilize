<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const user = computed(() => (page.props.auth as any)?.user)
const flash = computed(() => (page.props.flash as any) ?? {})
</script>

<template>
  <div class="drawer lg:drawer-open min-h-screen bg-base-200 text-base-content font-sans">
    <input id="erp-drawer" type="checkbox" class="drawer-toggle" />

    <!-- Area Konten Utama -->
    <div class="drawer-content flex flex-col min-h-screen">
      
      <!-- Top Navbar -->
      <header class="navbar bg-base-100 border-b border-base-300 sticky top-0 z-30 px-4 shadow-sm">
        
        <!-- Toggle button untuk Mobile -->
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
          
          <!-- Notifikasi Button -->
          <div class="dropdown dropdown-end">
            <button tabindex="0" class="btn btn-ghost btn-circle btn-sm">
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

      <!-- Alert Notifikasi Flash -->
      <div v-if="flash?.success" class="p-4 pb-0 max-w-7xl mx-auto w-full">
        <div role="alert" class="alert alert-success text-success-content shadow-sm flex items-center justify-between">
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ flash.success }}</span>
          </div>
        </div>
      </div>
      <div v-if="flash?.error" class="p-4 pb-0 max-w-7xl mx-auto w-full">
        <div role="alert" class="alert alert-error text-error-content shadow-sm flex items-center justify-between">
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ flash.error }}</span>
          </div>
        </div>
      </div>

      <!-- Main Page Content Slot -->
      <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto w-full">
        <slot />
      </main>

      <!-- Footer -->
      <footer class="footer footer-center p-4 border-t border-base-300 bg-base-100 text-base-content/60 text-xs mt-auto">
        <aside>
          <p>© 2026 Brilize School ERP • Sistem Informasi Akademik & Keuangan Terpadu</p>
        </aside>
      </footer>

    </div>

    <!-- Sidebar Navigation Drawer -->
    <aside class="drawer-side z-40">
      <label for="erp-drawer" aria-label="Tutup Sidebar" class="drawer-overlay"></label>
      
      <div class="menu p-4 w-72 min-h-full bg-base-100 text-base-content border-r border-base-300 flex flex-col justify-between">
        
        <div>
          <!-- Logo Brand -->
          <div class="px-2 py-4 mb-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-primary to-primary-focus flex items-center justify-center text-white font-extrabold text-xl shadow">
              🎓
            </div>
            <div>
              <div class="font-bold text-lg leading-none tracking-tight">Brilize ERP</div>
              <div class="text-xs opacity-60 mt-0.5">Portal Sekolah Terpadu</div>
            </div>
          </div>

          <!-- Menu Links -->
          <ul class="space-y-1">
            
            <li>
              <Link href="/" class="active:bg-primary font-medium flex items-center gap-3">
                <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Dashboard
              </Link>
            </li>

            <li class="menu-title mt-4 text-xs uppercase tracking-wider font-semibold opacity-60">AKADEMIK</li>
            <li>
              <Link href="/akademik/siswa" class="flex items-center gap-3">
                <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                Data Siswa
              </Link>
            </li>
            <li>
              <Link href="/akademik/guru" class="flex items-center gap-3">
                <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                </svg>
                Guru & PTK
              </Link>
            </li>
            <li>
              <Link href="/akademik/kelas" class="flex items-center gap-3">
                <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                Kelas & Rombel
              </Link>
            </li>
            <li>
              <Link href="/akademik/jadwal" class="flex items-center gap-3">
                <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Jadwal Pelajaran
              </Link>
            </li>
            <li>
              <Link href="/akademik/presensi" class="flex items-center gap-3">
                <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
                Presensi Harian
              </Link>
            </li>
            <li>
              <Link href="/akademik/nilai" class="flex items-center gap-3">
                <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Penilaian & Rapor
              </Link>
            </li>

            <li class="menu-title mt-4 text-xs uppercase tracking-wider font-semibold opacity-60">KEUANGAN (SPP)</li>
            <li>
              <Link href="/keuangan/tagihan" class="flex items-center gap-3">
                <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path>
                </svg>
                Tagihan SPP
              </Link>
            </li>
            <li>
              <Link href="/keuangan/transaksi" class="flex items-center gap-3">
                <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Kasir & Pembayaran
              </Link>
            </li>

            <li class="menu-title mt-4 text-xs uppercase tracking-wider font-semibold opacity-60">PENGATURAN</li>
            <li>
              <Link href="/users" class="flex items-center gap-3">
                <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                Manajemen User
              </Link>
            </li>
          </ul>
        </div>

        <!-- Logout Action di bagian bawah Sidebar -->
        <div class="pt-4 border-t border-base-200">
          <Link href="/logout" method="post" as="button" class="btn btn-outline btn-error btn-sm w-full gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
            Keluar Sesi
          </Link>
        </div>

      </div>
    </aside>

  </div>
</template>

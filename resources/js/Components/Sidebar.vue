<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

interface NavItem {
  label: string
  href: string
  icon: string
  roles?: string[]
  badge?: string
  badgeClass?: string
}

interface NavSection {
  title?: string
  roles?: string[]
  items: NavItem[]
}

const page = usePage()
const currentUrl = computed(() => page.url)
const userRoles = computed(() => (page.props.auth as any)?.user?.roles ?? [])

// Konfigurasi Navigasi Terpusat ERP Sekolah
const navigationSections: NavSection[] = [
  {
    items: [
      {
        label: 'Dashboard',
        href: '/',
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
      },
    ],
  },
  {
    title: 'AKADEMIK',
    items: [
      {
        label: 'Data Siswa',
        href: '/akademik/siswa',
        icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
      },
      {
        label: 'Guru & PTK',
        href: '/akademik/guru',
        icon: 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z',
      },
      {
        label: 'Mata Pelajaran',
        href: '/akademik/mata-pelajaran',
        icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
      },
      {
        label: 'Kelas & Rombel',
        href: '/akademik/kelas',
        icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
      },
      {
        label: 'Jadwal Pelajaran',
        href: '/akademik/jadwal',
        icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
      },
      {
        label: 'Presensi Harian',
        href: '/akademik/presensi',
        icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
      },
      {
        label: 'Penilaian & Rapor',
        href: '/akademik/nilai',
        icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
      },
    ],
  },
  {
    title: 'KEUANGAN (SPP)',
    items: [
      {
        label: 'Tagihan SPP',
        href: '/keuangan/tagihan',
        icon: 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z',
      },
      {
        label: 'Kasir & Pembayaran',
        href: '/keuangan/transaksi',
        icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
      },
    ],
  },
  {
    title: 'PENGATURAN',
    items: [
      {
        label: 'Manajemen User',
        href: '/users',
        icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
      },
      {
        label: 'Tahun Pelajaran',
        href: '/pengaturan/tahun-ajaran',
        icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
      },
    ],
  },
]

function isActive(href: string): boolean {
  if (href === '/') {
    return currentUrl.value === '/' || currentUrl.value === ''
  }
  return currentUrl.value.startsWith(href)
}

function hasPermission(roles?: string[]): boolean {
  if (!roles || roles.length === 0) return true
  return roles.some((role) => userRoles.value.includes(role))
}
</script>

<template>
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

        <!-- Looping Menu Navigation Sections -->
        <nav class="space-y-4">
          <div
            v-for="(section, secIdx) in navigationSections"
            :key="secIdx"
            class="space-y-1"
          >
            <!-- Section Title / Header -->
            <div
              v-if="section.title && hasPermission(section.roles)"
              class="menu-title px-2 py-1 text-xs uppercase tracking-wider font-semibold opacity-60"
            >
              {{ section.title }}
            </div>

            <!-- List Menu Items -->
            <ul class="space-y-1">
              <template v-for="item in section.items" :key="item.href">
                <li v-if="hasPermission(item.roles)">
                  <Link
                    :href="item.href"
                    class="flex items-center justify-between gap-3 rounded-xl transition-all"
                    :class="{
                      'bg-primary text-primary-content font-semibold shadow-sm': isActive(item.href),
                      'hover:bg-base-200 opacity-80 hover:opacity-100': !isActive(item.href),
                    }"
                  >
                    <div class="flex items-center gap-3">
                      <svg class="w-5 h-5 flex-shrink-0" :class="isActive(item.href) ? 'opacity-100' : 'opacity-70'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                      </svg>
                      <span>{{ item.label }}</span>
                    </div>

                    <!-- Optional Badge -->
                    <span
                      v-if="item.badge"
                      class="badge badge-xs font-semibold"
                      :class="item.badgeClass ?? 'badge-primary'"
                    >
                      {{ item.badge }}
                    </span>
                  </Link>
                </li>
              </template>
            </ul>
          </div>
        </nav>
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
</template>

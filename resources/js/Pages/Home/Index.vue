<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '../../Layouts/AppLayout.vue'

interface Props {
  auth?: {
    user?: {
      name: string
      identifier: string
      roles: string[]
    }
  }
  dashboard?: {
    academicYear: {
      name: string
      semester: string
      isActive: boolean
    }
    stats: {
      totalSiswa: { label: string; value: string; sub: string; badge: string; trend: string }
      totalGuru: { label: string; value: string; sub: string; badge: string; trend: string }
      presensiHariIni: { label: string; value: string; sub: string; badge: string; trend: string }
      pembayaranSPP: { label: string; value: string; sub: string; badge: string; trend: string }
    }
    recentActivities: Array<{
      id: number
      title: string
      actor: string
      time: string
      type: string
      badge: string
      badgeColor: string
    }>
    todaySchedule: Array<{
      id: number
      jam: string
      mapel: string
      kelas: string
      guru: string
      ruangan: string
    }>
    announcements: Array<{
      id: number
      title: string
      date: string
      author: string
      content: string
      isImportant: boolean
    }>
  }
}

const props = defineProps<Props>()

const greeting = (() => {
  const hour = new Date().getHours()
  if (hour < 11) return 'Selamat Pagi'
  if (hour < 15) return 'Selamat Siang'
  if (hour < 18) return 'Selamat Sore'
  return 'Selamat Malam'
})()
</script>

<template>
  <AppLayout>
    <Head title="Dashboard - Brilize School ERP" />

    <div class="space-y-6">
      
      <!-- Welcome Hero Banner -->
      <div class="bg-gradient-to-r from-primary via-primary/90 to-primary/80 text-primary-content rounded-3xl p-6 sm:p-8 shadow-lg relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur px-3 py-1 rounded-full text-xs font-medium text-white mb-3">
              <span>🌟</span>
              <span>Tahun Pelajaran {{ props.dashboard?.academicYear.name }} (Semester {{ props.dashboard?.academicYear.semester }})</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
              {{ greeting }}, {{ props.auth?.user?.name ?? 'Pengguna' }}!
            </h1>
            <p class="text-white/80 text-sm mt-1 max-w-xl">
              Selamat datang di pusat kendali operasional dan akademik sekolah. Pantau kehadiran, jadwal pembelajaran, dan transaksi keuangan secara real-time.
            </p>
          </div>

          <div class="flex items-center gap-2 flex-wrap">
            <Link href="/akademik/presensi" class="btn btn-sm sm:btn-md bg-white text-primary hover:bg-white/90 border-0 shadow-md font-semibold">
              📝 Presensi Siswa
            </Link>
            <Link href="/keuangan/transaksi" class="btn btn-sm sm:btn-md bg-white/20 hover:bg-white/30 text-white border-0 backdrop-blur font-semibold">
              💳 Kasir SPP
            </Link>
          </div>
        </div>
      </div>

      <!-- Quick Metrics Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        
        <!-- Total Siswa -->
        <div class="card bg-base-100 shadow border border-base-200">
          <div class="card-body p-5">
            <div class="flex items-center justify-between">
              <span class="text-xs font-semibold text-base-content/60 uppercase tracking-wider">
                {{ props.dashboard?.stats.totalSiswa.label }}
              </span>
              <div class="w-8 h-8 rounded-xl bg-info/10 text-info flex items-center justify-center font-bold text-sm">
                🎒
              </div>
            </div>
            <div class="text-3xl font-extrabold text-base-content mt-2">
              {{ props.dashboard?.stats.totalSiswa.value }}
            </div>
            <div class="flex items-center justify-between mt-2 pt-2 border-t border-base-200 text-xs">
              <span class="text-base-content/60">{{ props.dashboard?.stats.totalSiswa.sub }}</span>
              <span class="badge badge-sm badge-info">{{ props.dashboard?.stats.totalSiswa.badge }}</span>
            </div>
          </div>
        </div>

        <!-- Guru & PTK -->
        <div class="card bg-base-100 shadow border border-base-200">
          <div class="card-body p-5">
            <div class="flex items-center justify-between">
              <span class="text-xs font-semibold text-base-content/60 uppercase tracking-wider">
                {{ props.dashboard?.stats.totalGuru.label }}
              </span>
              <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-bold text-sm">
                👨‍🏫
              </div>
            </div>
            <div class="text-3xl font-extrabold text-base-content mt-2">
              {{ props.dashboard?.stats.totalGuru.value }}
            </div>
            <div class="flex items-center justify-between mt-2 pt-2 border-t border-base-200 text-xs">
              <span class="text-base-content/60">{{ props.dashboard?.stats.totalGuru.sub }}</span>
              <span class="badge badge-sm badge-primary">{{ props.dashboard?.stats.totalGuru.badge }}</span>
            </div>
          </div>
        </div>

        <!-- Kehadiran Hari Ini -->
        <div class="card bg-base-100 shadow border border-base-200">
          <div class="card-body p-5">
            <div class="flex items-center justify-between">
              <span class="text-xs font-semibold text-base-content/60 uppercase tracking-wider">
                {{ props.dashboard?.stats.presensiHariIni.label }}
              </span>
              <div class="w-8 h-8 rounded-xl bg-success/10 text-success flex items-center justify-center font-bold text-sm">
                📈
              </div>
            </div>
            <div class="text-3xl font-extrabold text-success mt-2">
              {{ props.dashboard?.stats.presensiHariIni.value }}
            </div>
            <div class="flex items-center justify-between mt-2 pt-2 border-t border-base-200 text-xs">
              <span class="text-base-content/60">{{ props.dashboard?.stats.presensiHariIni.sub }}</span>
              <span class="badge badge-sm badge-success">{{ props.dashboard?.stats.presensiHariIni.badge }}</span>
            </div>
          </div>
        </div>

        <!-- Pembayaran SPP -->
        <div class="card bg-base-100 shadow border border-base-200">
          <div class="card-body p-5">
            <div class="flex items-center justify-between">
              <span class="text-xs font-semibold text-base-content/60 uppercase tracking-wider">
                {{ props.dashboard?.stats.pembayaranSPP.label }}
              </span>
              <div class="w-8 h-8 rounded-xl bg-warning/10 text-warning flex items-center justify-center font-bold text-sm">
                💰
              </div>
            </div>
            <div class="text-2xl font-extrabold text-base-content mt-2 truncate">
              {{ props.dashboard?.stats.pembayaranSPP.value }}
            </div>
            <div class="flex items-center justify-between mt-2 pt-2 border-t border-base-200 text-xs">
              <span class="text-base-content/60">{{ props.dashboard?.stats.pembayaranSPP.sub }}</span>
              <span class="badge badge-sm badge-warning">{{ props.dashboard?.stats.pembayaranSPP.badge }}</span>
            </div>
          </div>
        </div>

      </div>

      <!-- Main Two-Column Content -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left 7 Cols: Jadwal Pelajaran & Pengumuman -->
        <div class="lg:col-span-7 space-y-6">
          
          <!-- Jadwal Pelajaran Hari Ini -->
          <div class="card bg-base-100 shadow border border-base-200">
            <div class="card-body p-5 sm:p-6">
              <div class="flex items-center justify-between pb-3 border-b border-base-200">
                <div class="flex items-center gap-2">
                  <span class="text-lg">🗓️</span>
                  <h2 class="font-bold text-base text-base-content">Jadwal Sesi Pembelajaran Hari Ini</h2>
                </div>
                <Link href="/akademik/jadwal" class="text-xs text-primary font-medium hover:underline">
                  Lihat Semua →
                </Link>
              </div>

              <div class="overflow-x-auto mt-2">
                <table class="table table-zebra table-sm w-full">
                  <thead>
                    <tr class="text-xs uppercase text-base-content/60">
                      <th>Waktu</th>
                      <th>Mata Pelajaran</th>
                      <th>Kelas</th>
                      <th>Guru Pengampu</th>
                      <th>Ruang</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in props.dashboard?.todaySchedule" :key="item.id" class="hover">
                      <td class="font-mono text-xs font-semibold text-primary whitespace-nowrap">{{ item.jam }}</td>
                      <td class="font-medium">{{ item.mapel }}</td>
                      <td>
                        <span class="badge badge-neutral badge-xs font-semibold">{{ item.kelas }}</span>
                      </td>
                      <td class="text-xs text-base-content/80">{{ item.guru }}</td>
                      <td class="text-xs opacity-60">{{ item.ruangan }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Pengumuman & Agenda Penting -->
          <div class="card bg-base-100 shadow border border-base-200">
            <div class="card-body p-5 sm:p-6">
              <div class="flex items-center justify-between pb-3 border-b border-base-200">
                <div class="flex items-center gap-2">
                  <span class="text-lg">📢</span>
                  <h2 class="font-bold text-base text-base-content">Pengumuman & Agenda Terkini</h2>
                </div>
              </div>

              <div class="space-y-4 mt-3">
                <div
                  v-for="item in props.dashboard?.announcements"
                  :key="item.id"
                  class="p-4 rounded-2xl border"
                  :class="item.isImportant ? 'bg-warning/5 border-warning/30' : 'bg-base-200/50 border-base-300'"
                >
                  <div class="flex items-center justify-between gap-2">
                    <span v-if="item.isImportant" class="badge badge-warning badge-xs font-semibold uppercase">Penting</span>
                    <span v-else class="badge badge-neutral badge-xs font-semibold uppercase">Informasi</span>
                    <span class="text-xs opacity-50 font-mono">{{ item.date }}</span>
                  </div>
                  <h3 class="font-bold text-sm text-base-content mt-1.5">{{ item.title }}</h3>
                  <p class="text-xs text-base-content/75 mt-1 leading-relaxed">{{ item.content }}</p>
                  <div class="text-[11px] text-base-content/50 mt-2 font-medium">Oleh: {{ item.author }}</div>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Right 5 Cols: Aktivitas Terkini & Pintasan Akses -->
        <div class="lg:col-span-5 space-y-6">
          
          <!-- Pintasan Modul Utama -->
          <div class="card bg-base-100 shadow border border-base-200">
            <div class="card-body p-5 sm:p-6">
              <div class="flex items-center gap-2 pb-3 border-b border-base-200">
                <span class="text-lg">⚡</span>
                <h2 class="font-bold text-base text-base-content">Pintasan Cepat ERP</h2>
              </div>

              <div class="grid grid-cols-2 gap-3 mt-3">
                <Link href="/akademik/siswa" class="p-3 rounded-2xl bg-base-200/60 hover:bg-primary/10 hover:text-primary transition-all border border-base-200 text-center flex flex-col items-center gap-1 group">
                  <span class="text-2xl group-hover:scale-110 transition-transform">👥</span>
                  <span class="text-xs font-semibold">Data Siswa</span>
                </Link>
                <Link href="/akademik/guru" class="p-3 rounded-2xl bg-base-200/60 hover:bg-primary/10 hover:text-primary transition-all border border-base-200 text-center flex flex-col items-center gap-1 group">
                  <span class="text-2xl group-hover:scale-110 transition-transform">👔</span>
                  <span class="text-xs font-semibold">Data Guru & PTK</span>
                </Link>
                <Link href="/akademik/nilai" class="p-3 rounded-2xl bg-base-200/60 hover:bg-primary/10 hover:text-primary transition-all border border-base-200 text-center flex flex-col items-center gap-1 group">
                  <span class="text-2xl group-hover:scale-110 transition-transform">📊</span>
                  <span class="text-xs font-semibold">Input Rapor</span>
                </Link>
                <Link href="/keuangan/tagihan" class="p-3 rounded-2xl bg-base-200/60 hover:bg-primary/10 hover:text-primary transition-all border border-base-200 text-center flex flex-col items-center gap-1 group">
                  <span class="text-2xl group-hover:scale-110 transition-transform">📑</span>
                  <span class="text-xs font-semibold">Tagihan SPP</span>
                </Link>
              </div>
            </div>
          </div>

          <!-- Aktivitas Sistem Real-Time -->
          <div class="card bg-base-100 shadow border border-base-200">
            <div class="card-body p-5 sm:p-6">
              <div class="flex items-center gap-2 pb-3 border-b border-base-200">
                <span class="text-lg">🕒</span>
                <h2 class="font-bold text-base text-base-content">Log Aktivitas Terkini</h2>
              </div>

              <div class="space-y-4 mt-3">
                <div v-for="act in props.dashboard?.recentActivities" :key="act.id" class="flex items-start gap-3 text-xs">
                  <div class="mt-0.5">
                    <span class="badge badge-xs font-bold" :class="act.badgeColor">{{ act.badge }}</span>
                  </div>
                  <div class="flex-1">
                    <div class="font-semibold text-base-content">{{ act.title }}</div>
                    <div class="text-[11px] text-base-content/60">{{ act.actor }}</div>
                  </div>
                  <div class="text-[10px] text-base-content/40 whitespace-nowrap">{{ act.time }}</div>
                </div>
              </div>
            </div>
          </div>

        </div>

      </div>

    </div>
  </AppLayout>
</template>

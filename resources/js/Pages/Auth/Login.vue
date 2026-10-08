<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

interface Props {
  status?: string | null
  errors?: Record<string, string>
  identifier?: string
}

const props = defineProps<Props>()

const showPassword = ref(false)

const form = useForm({
  identifier: props.identifier ?? '',
  password: '',
  remember: false,
})

function submit() {
  form.post('/login', {
    onFinish: () => {
      form.password = ''
    },
  })
}

function setDemoUser(type: 'admin' | 'guru' | 'siswa') {
  if (type === 'admin') {
    form.identifier = 'admin@sekolah.sch.id'
    form.password = 'password123'
  } else if (type === 'guru') {
    form.identifier = '198501152010011002' // NIP
    form.password = 'password123'
  } else if (type === 'siswa') {
    form.identifier = '0087654321' // NISN
    form.password = 'password123'
  }
}
</script>

<template>
  <Head title="Masuk - Portal ERP Sekolah" />

  <div class="min-h-screen bg-base-200 flex flex-col justify-center items-center p-4 sm:p-6 lg:p-8">
    <div class="w-full max-w-5xl grid grid-cols-1 lg:grid-cols-12 bg-base-100 rounded-3xl shadow-xl border border-base-300 overflow-hidden">
      
      <!-- Sisi Kiri: Branding & Informasi Portal -->
      <div class="lg:col-span-5 bg-gradient-to-br from-primary to-primary-focus text-primary-content p-8 lg:p-10 flex flex-col justify-between relative overflow-hidden">
        <!-- Background Pattern Decor -->
        <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -left-12 -top-12 w-48 h-48 bg-white/10 rounded-full blur-xl pointer-events-none"></div>

        <div>
          <!-- Logo Badge -->
          <div class="flex items-center gap-3 mb-8">
            <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center font-black text-2xl shadow-inner border border-white/30">
              🎓
            </div>
            <div>
              <h1 class="text-xl font-bold tracking-tight text-white leading-none">Brilize ERP</h1>
              <p class="text-xs text-white/80 mt-0.5">Sistem Informasi Sekolah</p>
            </div>
          </div>

          <div class="space-y-4">
            <span class="badge badge-warning badge-sm font-semibold uppercase tracking-wider text-xs">Portal Terpadu</span>
            <h2 class="text-2xl lg:text-3xl font-extrabold text-white leading-snug">
              Selamat Datang di Portal Akademik & Keuangan
            </h2>
            <p class="text-sm text-white/85 leading-relaxed">
              Akses cepat untuk Guru, Tenaga Kependidikan, Siswa, dan Manajemen Sekolah dalam satu gerbang terintegrasi.
            </p>
          </div>
        </div>

        <!-- Fitur Highlight -->
        <div class="mt-8 pt-6 border-t border-white/20 space-y-3 text-xs text-white/90">
          <div class="flex items-center gap-2.5">
            <svg class="w-4 h-4 text-warning flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>Presensi, Rapor & Penilaian Kurikulum</span>
          </div>
          <div class="flex items-center gap-2.5">
            <svg class="w-4 h-4 text-warning flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>Manajemen Tagihan & Pembayaran SPP</span>
          </div>
          <div class="flex items-center gap-2.5">
            <svg class="w-4 h-4 text-warning flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>Layanan Administrasi & Buku Induk Digital</span>
          </div>
        </div>
      </div>

      <!-- Sisi Kanan: Form Login -->
      <div class="lg:col-span-7 p-8 lg:p-12 flex flex-col justify-center">
        <div class="max-w-md w-full mx-auto space-y-6">
          
          <div>
            <h3 class="text-2xl font-bold tracking-tight text-base-content">Masuk ke Akun</h3>
            <p class="text-sm text-base-content/60 mt-1">
              Gunakan <b>NISN</b>, <b>NIP</b>, atau <b>Email</b> yang terdaftar pada sistem sekolah.
            </p>
          </div>

          <!-- Status Banner jika ada -->
          <div v-if="props.status" class="alert alert-info alert-soft text-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ props.status }}</span>
          </div>

          <!-- Form Input -->
          <form @submit.prevent="submit" class="space-y-4">
            
            <!-- Identifier Field -->
            <div class="form-control w-full">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase tracking-wider text-base-content/70">
                  NISN / NIP / Email / Username
                </span>
              </label>
              <div class="relative">
                <input
                  v-model="form.identifier"
                  type="text"
                  placeholder="Contoh: 19850115... atau 008765..."
                  autocomplete="username"
                  autofocus
                  class="input input-bordered w-full pl-10 focus:input-primary transition-all"
                  :class="{ 'input-error': form.errors.identifier || props.errors?.identifier }"
                />
                <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none opacity-40">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                </span>
              </div>
              <div v-if="form.errors.identifier || props.errors?.identifier" class="text-error text-xs mt-1 font-medium">
                {{ form.errors.identifier || props.errors?.identifier }}
              </div>
            </div>

            <!-- Password Field -->
            <div class="form-control w-full">
              <div class="flex justify-between items-center pb-1">
                <label class="label p-0">
                  <span class="label-text font-semibold text-xs uppercase tracking-wider text-base-content/70">
                    Kata Sandi
                  </span>
                </label>
                <a href="javascript:void(0)" class="text-xs text-primary hover:underline font-medium" title="Hubungi Bagian Tata Usaha (TU)">
                  Lupa sandi?
                </a>
              </div>
              <div class="relative">
                <input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="Masukkan kata sandi Anda"
                  autocomplete="current-password"
                  class="input input-bordered w-full pl-10 pr-10 focus:input-primary transition-all"
                  :class="{ 'input-error': form.errors.password || props.errors?.password }"
                />
                <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none opacity-40">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                  </svg>
                </span>
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute inset-y-0 right-3 flex items-center opacity-50 hover:opacity-100 transition-opacity"
                  tabindex="-1"
                >
                  <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                  </svg>
                  <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
                  </svg>
                </button>
              </div>
              <div v-if="form.errors.password || props.errors?.password" class="text-error text-xs mt-1 font-medium">
                {{ form.errors.password || props.errors?.password }}
              </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between pt-1">
              <label class="cursor-pointer label gap-2 p-0">
                <input
                  v-model="form.remember"
                  type="checkbox"
                  class="checkbox checkbox-primary checkbox-sm rounded"
                />
                <span class="label-text text-sm">Ingat saya di perangkat ini</span>
              </label>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
              <button
                type="submit"
                class="btn btn-primary w-full text-white shadow-md hover:shadow-lg transition-all"
                :disabled="form.processing"
              >
                <span v-if="form.processing" class="loading loading-spinner loading-sm"></span>
                <span v-else>Masuk ke Portal</span>
              </button>
            </div>
          </form>

          <!-- Quick Fill Helper (Mode Dev / Demo) -->
          <div class="pt-4 border-t border-base-200">
            <div class="text-xs text-base-content/50 mb-2 font-medium text-center">
              Pintasan Akun Demo:
            </div>
            <div class="flex justify-center gap-2">
              <button
                type="button"
                @click="setDemoUser('admin')"
                class="btn btn-xs btn-outline btn-ghost"
              >
                🔑 Admin
              </button>
              <button
                type="button"
                @click="setDemoUser('guru')"
                class="btn btn-xs btn-outline btn-ghost"
              >
                👨‍🏫 Guru (NIP)
              </button>
              <button
                type="button"
                @click="setDemoUser('siswa')"
                class="btn btn-xs btn-outline btn-ghost"
              >
                🎒 Siswa (NISN)
              </button>
            </div>
          </div>

          <!-- Bantuan Kontak -->
          <p class="text-center text-xs text-base-content/50 pt-2">
            Mengalami kendala akun? Silakan hubungi <b>Administrator TU Sekolah</b>.
          </p>

        </div>
      </div>

    </div>
  </div>
</template>

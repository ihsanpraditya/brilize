<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import AppLayout from '../../Layouts/AppLayout.vue'

interface UserItem {
  id: number
  name: string
  email?: string | null
  username?: string | null
  identifierNumber?: string | null
  roles: string[]
  status: 'active' | 'inactive' | 'suspended'
  statusLabel: string
  statusBadge: string
  phone?: string | null
  avatarUrl?: string | null
  lastLoginAt?: string | null
  createdAt: string
}

interface RoleOption {
  value: string
  label: string
}

interface StatusOption {
  value: string
  label: string
  badge?: string
}

interface Props {
  users: UserItem[]
  filters?: {
    q?: string
    status?: string
  }
  options?: {
    roles: RoleOption[]
    statuses: StatusOption[]
  }
}

const props = withDefaults(defineProps<Props>(), {
  users: () => [],
  filters: () => ({}),
  options: () => ({
    roles: [],
    statuses: [],
  }),
})

const page = usePage()
const currentAuthUserId = computed(() => (page.props.auth as any)?.user?.id)

// Master opsi peran jika props options belum disediakan
const availableRoles = computed<RoleOption[]>(() => {
  if (props.options?.roles && props.options.roles.length > 0) {
    return props.options.roles
  }
  return [
    { value: 'ROLE_SUPER_ADMIN', label: 'Super Administrator' },
    { value: 'ROLE_KEPSEK', label: 'Kepala Sekolah' },
    { value: 'ROLE_TU', label: 'Staf Tata Usaha (TU)' },
    { value: 'ROLE_BENDAHARA', label: 'Bendahara / Keuangan' },
    { value: 'ROLE_GURU', label: 'Guru / Tenaga Pendidik' },
    { value: 'ROLE_SISWA', label: 'Siswa / Peserta Didik' },
    { value: 'ROLE_WALI', label: 'Orang Tua / Wali Murid' },
  ]
})

const availableStatuses = computed<StatusOption[]>(() => {
  if (props.options?.statuses && props.options.statuses.length > 0) {
    return props.options.statuses
  }
  return [
    { value: 'active', label: 'Aktif', badge: 'badge-success' },
    { value: 'inactive', label: 'Nonaktif', badge: 'badge-neutral' },
    { value: 'suspended', label: 'Ditangguhkan', badge: 'badge-error' },
  ]
})

// Search & Filter State
const search = ref(props.filters?.q ?? '')
const selectedStatus = ref(props.filters?.status ?? '')
const selectedRole = ref('')

function applyFilter() {
  router.get(
    '/users',
    {
      q: search.value || undefined,
      status: selectedStatus.value || undefined,
    },
    {
      preserveState: true,
      replace: true,
    }
  )
}

watch(selectedStatus, () => {
  applyFilter()
})

let searchTimeout: any = null
watch(search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilter()
  }, 400)
})

function resetFilter() {
  search.value = ''
  selectedStatus.value = ''
  selectedRole.value = ''
  applyFilter()
}

// Client-side filter untuk tab peran aktif
const filteredUsers = computed(() => {
  if (!selectedRole.value) {
    return props.users
  }
  return props.users.filter((user) => user.roles.includes(selectedRole.value))
})

// Statistik Metrik Pengguna
const stats = computed(() => {
  const total = props.users.length
  const active = props.users.filter((u) => u.status === 'active').length
  const ptk = props.users.filter((u) =>
    u.roles.some((r) => ['ROLE_GURU', 'ROLE_TU', 'ROLE_KEPSEK', 'ROLE_BENDAHARA'].includes(r))
  ).length
  const siswa = props.users.filter((u) => u.roles.includes('ROLE_SISWA')).length

  return { total, active, ptk, siswa }
})

// Modal Form State
const isModalOpen = ref(false)
const modalMode = ref<'create' | 'edit'>('create')
const editingId = ref<number | null>(null)

const form = useForm({
  name: '',
  username: '',
  email: '',
  identifier_number: '',
  phone: '',
  password: '',
  roles: ['ROLE_SISWA'] as string[],
  status: 'active',
})

function openCreateModal() {
  modalMode.value = 'create'
  editingId.value = null
  form.reset()
  form.clearErrors()
  form.roles = ['ROLE_SISWA']
  form.status = 'active'
  isModalOpen.value = true
}

function openEditModal(item: UserItem) {
  modalMode.value = 'edit'
  editingId.value = item.id
  form.clearErrors()
  form.name = item.name
  form.username = item.username ?? ''
  form.email = item.email ?? ''
  form.identifier_number = item.identifierNumber ?? ''
  form.phone = item.phone ?? ''
  form.password = ''
  // Pastikan peran yang dipilih valid dan singkirkan ROLE_USER bawaan Symfony dari form
  const rawRoles = item.roles.filter((r) => r !== 'ROLE_USER')
  form.roles = rawRoles.length > 0 ? rawRoles : ['ROLE_SISWA']
  form.status = item.status
  isModalOpen.value = true
}

function closeModal() {
  isModalOpen.value = false
  form.reset()
  form.clearErrors()
}

function toggleRole(roleValue: string) {
  const index = form.roles.indexOf(roleValue)
  if (index === -1) {
    form.roles.push(roleValue)
  } else {
    // Sisakan minimal 1 role
    if (form.roles.length > 1) {
      form.roles.splice(index, 1)
    }
  }
}

function submit() {
  if (modalMode.value === 'create') {
    form.post('/users', {
      onSuccess: () => closeModal(),
    })
  } else if (editingId.value !== null) {
    form.put(`/users/${editingId.value}`, {
      onSuccess: () => closeModal(),
    })
  }
}

function confirmDelete(item: UserItem) {
  if (item.id === currentAuthUserId.value) {
    alert('Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.')
    return
  }

  if (confirm(`Apakah Anda yakin ingin menghapus akun pengguna "${item.name}"? Aksi ini permanen.`)) {
    router.delete(`/users/${item.id}`, {
      preserveScroll: true,
    })
  }
}

// Helpers untuk label & badge peran
function getRoleBadge(role: string): string {
  const badgeMap: Record<string, string> = {
    ROLE_SUPER_ADMIN: 'badge-error',
    ROLE_KEPSEK: 'badge-warning',
    ROLE_TU: 'badge-info',
    ROLE_BENDAHARA: 'badge-accent',
    ROLE_GURU: 'badge-primary',
    ROLE_SISWA: 'badge-neutral',
    ROLE_WALI: 'badge-outline',
  }
  return badgeMap[role] ?? 'badge-ghost'
}

function getRoleLabel(role: string): string {
  const labelMap: Record<string, string> = {
    ROLE_SUPER_ADMIN: 'Super Admin',
    ROLE_KEPSEK: 'Kepala Sekolah',
    ROLE_TU: 'Tata Usaha',
    ROLE_BENDAHARA: 'Bendahara',
    ROLE_GURU: 'Guru',
    ROLE_SISWA: 'Siswa',
    ROLE_WALI: 'Wali Murid',
    ROLE_USER: 'User',
  }
  return labelMap[role] ?? role
}
</script>

<template>
  <AppLayout>
    <Head title="Manajemen Pengguna - ERP Sekolah" />

    <div class="space-y-6">

      <!-- Header Halaman -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-base-content">Manajemen Pengguna & Hak Akses</h1>
          <p class="text-sm opacity-70">
            Kelola master akun pengguna, nomor identitas sekolah (NIP/NISN), peran (role), dan status akses sistem.
          </p>
        </div>
        <div>
          <button @click="openCreateModal" class="btn btn-primary btn-sm md:btn-md shadow-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Pengguna Baru
          </button>
        </div>
      </div>

      <!-- Kartu Statistik Ringkasan -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat bg-base-100 rounded-2xl shadow-sm border border-base-200 p-4">
          <div class="stat-title text-xs font-semibold uppercase">Total Pengguna</div>
          <div class="stat-value text-primary text-2xl mt-1">{{ stats.total }}</div>
          <div class="stat-desc text-xs mt-1">Akun terdaftar dalam database</div>
        </div>

        <div class="stat bg-base-100 rounded-2xl shadow-sm border border-base-200 p-4">
          <div class="stat-title text-xs font-semibold uppercase">Pengguna Aktif</div>
          <div class="stat-value text-success text-2xl mt-1">{{ stats.active }}</div>
          <div class="stat-desc text-xs mt-1">Dapat mengakses sistem portal</div>
        </div>

        <div class="stat bg-base-100 rounded-2xl shadow-sm border border-base-200 p-4">
          <div class="stat-title text-xs font-semibold uppercase">Guru & Tenaga Pendidik / TU</div>
          <div class="stat-value text-info text-2xl mt-1">{{ stats.ptk }}</div>
          <div class="stat-desc text-xs mt-1">Akun pendidik & kependidikan</div>
        </div>

        <div class="stat bg-base-100 rounded-2xl shadow-sm border border-base-200 p-4">
          <div class="stat-title text-xs font-semibold uppercase">Siswa / Peserta Didik</div>
          <div class="stat-value text-secondary text-2xl mt-1">{{ stats.siswa }}</div>
          <div class="stat-desc text-xs mt-1">Akun peserta didik aktif</div>
        </div>
      </div>

      <!-- Filter Bar: Search, Status, & Quick Role Tabs -->
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <!-- Quick Role Tabs -->
        <div class="tabs tabs-box bg-base-200 p-1 rounded-2xl overflow-x-auto flex-nowrap w-fit max-w-full">
          <button
            @click="selectedRole = ''"
            class="tab text-xs font-semibold whitespace-nowrap"
            :class="{ 'tab-active bg-base-100 shadow-sm text-primary': selectedRole === '' }"
          >
            Semua Peran ({{ props.users.length }})
          </button>
          <button
            @click="selectedRole = 'ROLE_SUPER_ADMIN'"
            class="tab text-xs font-semibold whitespace-nowrap"
            :class="{ 'tab-active bg-base-100 shadow-sm text-primary': selectedRole === 'ROLE_SUPER_ADMIN' }"
          >
            Super Admin
          </button>
          <button
            @click="selectedRole = 'ROLE_GURU'"
            class="tab text-xs font-semibold whitespace-nowrap"
            :class="{ 'tab-active bg-base-100 shadow-sm text-primary': selectedRole === 'ROLE_GURU' }"
          >
            Guru
          </button>
          <button
            @click="selectedRole = 'ROLE_TU'"
            class="tab text-xs font-semibold whitespace-nowrap"
            :class="{ 'tab-active bg-base-100 shadow-sm text-primary': selectedRole === 'ROLE_TU' }"
          >
            Tata Usaha
          </button>
          <button
            @click="selectedRole = 'ROLE_SISWA'"
            class="tab text-xs font-semibold whitespace-nowrap"
            :class="{ 'tab-active bg-base-100 shadow-sm text-primary': selectedRole === 'ROLE_SISWA' }"
          >
            Siswa
          </button>
          <button
            @click="selectedRole = 'ROLE_WALI'"
            class="tab text-xs font-semibold whitespace-nowrap"
            :class="{ 'tab-active bg-base-100 shadow-sm text-primary': selectedRole === 'ROLE_WALI' }"
          >
            Wali Murid
          </button>
        </div>

        <!-- Filter Controls -->
        <div class="flex items-center gap-3 flex-wrap">
          <select v-model="selectedStatus" class="select select-bordered select-sm text-xs">
            <option value="">Semua Status Akun</option>
            <option v-for="s in availableStatuses" :key="s.value" :value="s.value">
              {{ s.label }}
            </option>
          </select>

          <div class="relative">
            <input
              v-model="search"
              type="text"
              placeholder="Cari nama / email / NIP / NISN..."
              class="input input-bordered input-sm w-56 sm:w-72 text-xs pr-8"
            />
            <span v-if="search" @click="search = ''" class="absolute right-2.5 top-2 text-xs opacity-50 cursor-pointer hover:opacity-100">
              ✕
            </span>
          </div>

          <button
            v-if="search || selectedStatus || selectedRole"
            @click="resetFilter"
            class="btn btn-ghost btn-sm text-xs text-error"
            title="Reset semua filter pencarian"
          >
            Reset
          </button>
        </div>
      </div>

      <!-- Tabel Daftar Pengguna -->
      <div class="card bg-base-100 shadow border border-base-200">
        <div class="card-body p-0">
          <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
              <thead>
                <tr class="text-xs uppercase text-base-content/60 bg-base-200/50">
                  <th>Pengguna & Identitas</th>
                  <th>Kontak</th>
                  <th>Peran / Hak Akses</th>
                  <th>Status Akun</th>
                  <th>Login Terakhir</th>
                  <th class="text-right">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in filteredUsers" :key="item.id" class="hover">
                  <!-- Pengguna & Identitas -->
                  <td>
                    <div class="flex items-center gap-3">
                      <div class="avatar placeholder">
                        <div class="w-10 h-10 rounded-full bg-primary/10 text-primary font-bold text-sm shadow-sm">
                          {{ item.name.charAt(0).toUpperCase() }}
                        </div>
                      </div>
                      <div>
                        <div class="font-bold text-sm text-base-content">{{ item.name }}</div>
                        <div class="flex items-center gap-2 text-xs opacity-70">
                          <span v-if="item.username" class="font-mono text-primary font-medium">@{{ item.username }}</span>
                          <span v-if="item.identifierNumber" class="badge badge-xs badge-ghost font-mono">
                            ID: {{ item.identifierNumber }}
                          </span>
                        </div>
                        <div class="text-[11px] opacity-40 mt-0.5">Dibuat: {{ item.createdAt }}</div>
                      </div>
                    </div>
                  </td>

                  <!-- Kontak -->
                  <td>
                    <div class="space-y-0.5">
                      <div v-if="item.email" class="text-xs flex items-center gap-1.5 text-base-content/80">
                        <svg class="w-3.5 h-3.5 opacity-50 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span class="truncate max-w-[180px]">{{ item.email }}</span>
                      </div>
                      <div v-if="item.phone" class="text-xs font-mono flex items-center gap-1.5 opacity-70">
                        <svg class="w-3.5 h-3.5 opacity-50 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <span>{{ item.phone }}</span>
                      </div>
                      <span v-if="!item.email && !item.phone" class="text-xs opacity-40 italic">
                        - Tidak ada kontak -
                      </span>
                    </div>
                  </td>

                  <!-- Peran / Hak Akses -->
                  <td>
                    <div class="flex flex-wrap gap-1 max-w-[220px]">
                      <template v-for="r in item.roles" :key="r">
                        <span
                          v-if="r !== 'ROLE_USER'"
                          class="badge badge-sm font-semibold"
                          :class="getRoleBadge(r)"
                        >
                          {{ getRoleLabel(r) }}
                        </span>
                      </template>
                    </div>
                  </td>

                  <!-- Status Akun -->
                  <td>
                    <span class="badge badge-sm font-semibold" :class="item.statusBadge">
                      {{ item.statusLabel }}
                    </span>
                  </td>

                  <!-- Login Terakhir -->
                  <td>
                    <div v-if="item.lastLoginAt" class="text-xs font-mono opacity-80">
                      {{ item.lastLoginAt }}
                    </div>
                    <span v-else class="text-xs opacity-40 italic">Belum pernah login</span>
                  </td>

                  <!-- Aksi -->
                  <td class="text-right space-x-1 whitespace-nowrap">
                    <button
                      @click="openEditModal(item)"
                      class="btn btn-ghost btn-xs text-primary font-semibold"
                    >
                      Edit
                    </button>
                    <button
                      @click="confirmDelete(item)"
                      class="btn btn-ghost btn-xs text-error font-semibold"
                      :disabled="item.id === currentAuthUserId"
                      :title="item.id === currentAuthUserId ? 'Tidak dapat menghapus akun sendiri' : 'Hapus pengguna'"
                    >
                      Hapus
                    </button>
                  </td>
                </tr>

                <!-- State Kosong -->
                <tr v-if="filteredUsers.length === 0">
                  <td colspan="6" class="text-center py-12 opacity-60">
                    <div class="flex flex-col items-center justify-center space-y-2">
                      <svg class="w-10 h-10 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                      </svg>
                      <p class="text-sm">Tidak ditemukan pengguna yang sesuai kriteria pencarian.</p>
                      <button @click="resetFilter" class="btn btn-link btn-xs text-primary">
                        Bersihkan filter
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>

    <!-- Modal Form Tambah / Edit Pengguna -->
    <dialog class="modal" :class="{ 'modal-open': isModalOpen }">
      <div class="modal-box max-w-2xl max-h-[90vh] overflow-y-auto">
        <h3 class="font-bold text-lg text-base-content mb-4 flex items-center gap-2">
          <span>{{ modalMode === 'create' ? '➕ Tambah Pengguna Baru' : '✏️ Edit Data Pengguna' }}</span>
        </h3>

        <form @submit.prevent="submit" class="space-y-4">
          <!-- Nama Lengkap -->
          <div class="form-control w-full">
            <label class="label pb-1">
              <span class="label-text font-semibold text-xs uppercase">Nama Lengkap *</span>
            </label>
            <input
              v-model="form.name"
              type="text"
              placeholder="Contoh: Budi Santoso, S.Pd."
              class="input input-bordered input-sm w-full"
              :class="{ 'input-error': form.errors.name }"
              required
            />
            <div v-if="form.errors.name" class="text-error text-xs mt-1">{{ form.errors.name }}</div>
          </div>

          <!-- Username & Email -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="form-control w-full">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase">Username</span>
                <span class="label-text-alt opacity-50">Untuk login</span>
              </label>
              <input
                v-model="form.username"
                type="text"
                placeholder="Contoh: budi_santoso"
                class="input input-bordered input-sm w-full font-mono"
                :class="{ 'input-error': form.errors.username }"
              />
              <div v-if="form.errors.username" class="text-error text-xs mt-1">{{ form.errors.username }}</div>
            </div>

            <div class="form-control w-full">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase">Alamat Email</span>
                <span class="label-text-alt opacity-50">Untuk login / notifikasi</span>
              </label>
              <input
                v-model="form.email"
                type="email"
                placeholder="budi@sekolah.sch.id"
                class="input input-bordered input-sm w-full"
                :class="{ 'input-error': form.errors.email }"
              />
              <div v-if="form.errors.email" class="text-error text-xs mt-1">{{ form.errors.email }}</div>
            </div>
          </div>

          <!-- Nomor Identitas (NIP/NISN) & No HP -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="form-control w-full">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase">NIP / NISN / No. Identitas</span>
                <span class="label-text-alt opacity-50">Opsional</span>
              </label>
              <input
                v-model="form.identifier_number"
                type="text"
                placeholder="19850115... atau 0087654321"
                class="input input-bordered input-sm w-full font-mono"
                :class="{ 'input-error': form.errors.identifier_number }"
              />
              <div v-if="form.errors.identifier_number" class="text-error text-xs mt-1">
                {{ form.errors.identifier_number }}
              </div>
            </div>

            <div class="form-control w-full">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase">Nomor HP / WhatsApp</span>
                <span class="label-text-alt opacity-50">Opsional</span>
              </label>
              <input
                v-model="form.phone"
                type="text"
                placeholder="081234567890"
                class="input input-bordered input-sm w-full font-mono"
                :class="{ 'input-error': form.errors.phone }"
              />
              <div v-if="form.errors.phone" class="text-error text-xs mt-1">{{ form.errors.phone }}</div>
            </div>
          </div>

          <!-- Kata Sandi -->
          <div class="form-control w-full p-3 bg-base-200/50 rounded-2xl border border-base-200">
            <label class="label pb-1">
              <span class="label-text font-semibold text-xs uppercase">
                Kata Sandi (Password) {{ modalMode === 'create' ? '*' : '(Opsional)' }}
              </span>
              <span v-if="modalMode === 'edit'" class="label-text-alt opacity-60">
                Kosongkan jika tidak ingin mengubah sandi
              </span>
            </label>
            <input
              v-model="form.password"
              type="password"
              :placeholder="modalMode === 'create' ? 'Minimal 6 karakter' : 'Masukkan sandi baru jika ingin diubah'"
              class="input input-bordered input-sm w-full"
              :class="{ 'input-error': form.errors.password }"
              :required="modalMode === 'create'"
            />
            <div v-if="form.errors.password" class="text-error text-xs mt-1">{{ form.errors.password }}</div>
          </div>

          <!-- Status Akun -->
          <div class="form-control w-full">
            <label class="label pb-1">
              <span class="label-text font-semibold text-xs uppercase">Status Akun *</span>
            </label>
            <div class="grid grid-cols-3 gap-2">
              <button
                type="button"
                v-for="st in availableStatuses"
                :key="st.value"
                @click="form.status = st.value"
                class="btn btn-sm text-xs font-semibold"
                :class="form.status === st.value ? 'btn-neutral border-primary text-primary-content shadow-sm' : 'btn-outline opacity-60'"
              >
                {{ st.label }}
              </button>
            </div>
          </div>

          <!-- Pilihan Peran / Hak Akses -->
          <div class="form-control w-full pt-1">
            <label class="label pb-1">
              <span class="label-text font-semibold text-xs uppercase">Peran & Hak Akses (Role) *</span>
              <span class="label-text-alt opacity-50">Dapat memilih lebih dari satu</span>
            </label>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-3 bg-base-200/40 rounded-2xl border border-base-200">
              <label
                v-for="role in availableRoles"
                :key="role.value"
                class="flex items-center gap-2.5 p-2 rounded-xl cursor-pointer transition hover:bg-base-200"
                :class="{ 'bg-base-100 shadow-xs': form.roles.includes(role.value) }"
              >
                <input
                  type="checkbox"
                  class="checkbox checkbox-primary checkbox-xs rounded"
                  :checked="form.roles.includes(role.value)"
                  @change="toggleRole(role.value)"
                />
                <span class="text-xs font-medium">{{ role.label }}</span>
              </label>
            </div>
            <div v-if="form.errors.roles" class="text-error text-xs mt-1">{{ form.errors.roles }}</div>
          </div>

          <!-- Action Buttons -->
          <div class="modal-action pt-4 border-t border-base-200">
            <button
              type="button"
              @click="closeModal"
              class="btn btn-ghost btn-sm"
              :disabled="form.processing"
            >
              Batal
            </button>
            <button
              type="submit"
              class="btn btn-primary btn-sm"
              :disabled="form.processing"
            >
              <span v-if="form.processing" class="loading loading-spinner loading-xs"></span>
              <span>{{ modalMode === 'create' ? 'Simpan Pengguna' : 'Perbarui Pengguna' }}</span>
            </button>
          </div>
        </form>
      </div>

      <form method="dialog" class="modal-backdrop">
        <button @click="closeModal">close</button>
      </form>
    </dialog>
  </AppLayout>
</template>

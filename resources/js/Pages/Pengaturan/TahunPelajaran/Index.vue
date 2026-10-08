<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '../../../Layouts/AppLayout.vue'

interface TahunPelajaranItem {
  id: number
  tahun: string
  semester: 'ganjil' | 'genap'
  semesterLabel: string
  isActive: boolean
  tanggalMulai?: string | null
  tanggalSelesai?: string | null
  namaLengkap: string
  createdAt: string
}

interface Props {
  tahunPelajaranList: TahunPelajaranItem[]
  activeTahunPelajaran?: TahunPelajaranItem | null
}

const props = defineProps<Props>()

const isModalOpen = ref(false)
const modalMode = ref<'create' | 'edit'>('create')
const editingId = ref<number | null>(null)

const form = useForm({
  tahun: '',
  semester: 'ganjil',
  is_active: false,
  tanggal_mulai: '',
  tanggal_selesai: '',
})

function openCreateModal() {
  modalMode.value = 'create'
  editingId.value = null
  form.reset()
  form.clearErrors()
  form.semester = 'ganjil'
  form.is_active = false
  isModalOpen.value = true
}

function openEditModal(item: TahunPelajaranItem) {
  modalMode.value = 'edit'
  editingId.value = item.id
  form.clearErrors()
  form.tahun = item.tahun
  form.semester = item.semester
  form.tanggal_mulai = item.tanggalMulai ?? ''
  form.tanggalSelesai = item.tanggalSelesai ?? ''
  isModalOpen.value = true
}

function closeModal() {
  isModalOpen.value = false
  form.reset()
}

function submit() {
  if (modalMode.value === 'create') {
    form.post('/pengaturan/tahun-ajaran', {
      onSuccess: () => closeModal(),
    })
  } else if (editingId.value !== null) {
    form.put(`/pengaturan/tahun-ajaran/${editingId.value}`, {
      onSuccess: () => closeModal(),
    })
  }
}

function setActive(item: TahunPelajaranItem) {
  if (confirm(`Apakah Anda yakin ingin mengaktifkan Tahun Pelajaran "${item.namaLengkap}" sebagai acuan aktif sistem?`)) {
    router.post(`/pengaturan/tahun-ajaran/${item.id}/set-active`, {}, {
      preserveScroll: true,
    })
  }
}

function hapus(item: TahunPelajaranItem) {
  if (item.isActive) {
    alert('Tahun Pelajaran yang sedang aktif tidak dapat dihapus.')
    return
  }

  if (confirm(`Apakah Anda yakin ingin menghapus Tahun Pelajaran "${item.namaLengkap}"?`)) {
    router.delete(`/pengaturan/tahun-ajaran/${item.id}`, {
      preserveScroll: true,
    })
  }
}
</script>

<template>
  <AppLayout>
    <Head title="Pengaturan Tahun Pelajaran - ERP Sekolah" />

    <div class="space-y-6">
      
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-base-content">Pengaturan Tahun Pelajaran</h1>
          <p class="text-sm opacity-70">Kelola master tahun akademik dan penentuan semester aktif pada sistem sekolah.</p>
        </div>
        <div>
          <button @click="openCreateModal" class="btn btn-primary btn-sm md:btn-md shadow-sm">
            + Tambah Tahun Pelajaran
          </button>
        </div>
      </div>

      <!-- Highlight Card: Tahun Pelajaran Aktif -->
      <div class="card bg-gradient-to-r from-primary/10 via-base-100 to-base-100 border border-primary/20 shadow-sm">
        <div class="card-body p-5 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-primary text-primary-content flex items-center justify-center text-2xl font-black shadow-sm">
              🗓️
            </div>
            <div>
              <div class="text-xs font-semibold text-primary uppercase tracking-wider">Tahun Pelajaran Acuan Sistem</div>
              <div class="text-xl sm:text-2xl font-black text-base-content mt-0.5">
                {{ props.activeTahunPelajaran?.namaLengkap ?? 'Belum ada tahun pelajaran aktif' }}
              </div>
              <div class="text-xs opacity-60 mt-0.5">
                Periode: {{ props.activeTahunPelajaran?.tanggalMulai ?? '-' }} s/d {{ props.activeTahunPelajaran?.tanggalSelesai ?? '-' }}
              </div>
            </div>
          </div>
          <div>
            <span class="badge badge-success badge-lg font-semibold gap-1.5 py-3">
              <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
              Aktif Digunakan
            </span>
          </div>
        </div>
      </div>

      <!-- Tabel Daftar Tahun Pelajaran -->
      <div class="card bg-base-100 shadow border border-base-200">
        <div class="card-body p-0">
          <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
              <thead>
                <tr class="text-xs uppercase text-base-content/60 bg-base-200/50">
                  <th>Tahun Pelajaran</th>
                  <th>Semester</th>
                  <th>Periode Tanggal</th>
                  <th>Status Sistem</th>
                  <th class="text-right">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in props.tahunPelajaranList" :key="item.id" class="hover">
                  <td>
                    <div class="font-bold text-base">{{ item.tahun }}</div>
                    <div class="text-[11px] opacity-50">Dibuat: {{ item.createdAt }}</div>
                  </td>
                  <td>
                    <span
                      class="badge badge-sm font-semibold uppercase"
                      :class="item.semester === 'ganjil' ? 'badge-primary badge-outline' : 'badge-secondary badge-outline'"
                    >
                      {{ item.semesterLabel }}
                    </span>
                  </td>
                  <td>
                    <div v-if="item.tanggalMulai || item.tanggalSelesai" class="text-xs font-mono">
                      {{ item.tanggalMulai ?? '?' }} s/d {{ item.tanggalSelesai ?? '?' }}
                    </div>
                    <span v-else class="text-xs opacity-40 italic">- Belum diset -</span>
                  </td>
                  <td>
                    <span
                      v-if="item.isActive"
                      class="badge badge-success badge-sm font-semibold gap-1"
                    >
                      <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                      Aktif
                    </span>
                    <span v-else class="badge badge-neutral badge-sm opacity-60">
                      Nonaktif
                    </span>
                  </td>
                  <td class="text-right space-x-2">
                    <button
                      v-if="!item.isActive"
                      @click="setActive(item)"
                      class="btn btn-outline btn-success btn-xs"
                      title="Setel sebagai acuan sistem"
                    >
                      Jadikan Aktif
                    </button>
                    <button
                      @click="openEditModal(item)"
                      class="btn btn-ghost btn-xs text-primary"
                    >
                      Edit
                    </button>
                    <button
                      v-if="!item.isActive"
                      @click="hapus(item)"
                      class="btn btn-ghost btn-xs text-error"
                    >
                      Hapus
                    </button>
                  </td>
                </tr>

                <tr v-if="props.tahunPelajaranList.length === 0">
                  <td colspan="5" class="text-center py-10 opacity-60">
                    Belum ada data Tahun Pelajaran. Klik tombol <b>+ Tambah Tahun Pelajaran</b> di atas.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>

    <!-- Modal Form Tambah / Edit -->
    <dialog class="modal" :class="{ 'modal-open': isModalOpen }">
      <div class="modal-box max-w-lg">
        <h3 class="font-bold text-lg text-base-content mb-4">
          {{ modalMode === 'create' ? 'Tambah Tahun Pelajaran' : 'Edit Tahun Pelajaran' }}
        </h3>

        <form @submit.prevent="submit" class="space-y-4">
          
          <!-- Tahun (Format YYYY/YYYY) -->
          <div class="form-control w-full">
            <label class="label pb-1">
              <span class="label-text font-semibold text-xs uppercase">Tahun Pelajaran *</span>
              <span class="label-text-alt opacity-50">Contoh: 2026/2027</span>
            </label>
            <input
              v-model="form.tahun"
              type="text"
              placeholder="2026/2027"
              class="input input-bordered w-full"
              :class="{ 'input-error': form.errors.tahun }"
              required
            />
            <div v-if="form.errors.tahun" class="text-error text-xs mt-1">{{ form.errors.tahun }}</div>
          </div>

          <!-- Semester -->
          <div class="form-control w-full">
            <label class="label pb-1">
              <span class="label-text font-semibold text-xs uppercase">Semester *</span>
            </label>
            <select v-model="form.semester" class="select select-bordered w-full">
              <option value="ganjil">Semester Ganjil</option>
              <option value="genap">Semester Genap</option>
            </select>
          </div>

          <!-- Tanggal Mulai & Tanggal Selesai -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="form-control w-full">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase">Tanggal Mulai</span>
              </label>
              <input
                v-model="form.tanggal_mulai"
                type="date"
                class="input input-bordered w-full"
              />
            </div>
            <div class="form-control w-full">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase">Tanggal Selesai</span>
              </label>
              <input
                v-model="form.tanggal_selesai"
                type="date"
                class="input input-bordered w-full"
              />
            </div>
          </div>

          <!-- Opsi Langsung Aktifkan (Hanya saat create) -->
          <div v-if="modalMode === 'create'" class="form-control pt-2">
            <label class="label cursor-pointer justify-start gap-3">
              <input
                v-model="form.is_active"
                type="checkbox"
                class="checkbox checkbox-primary checkbox-sm rounded"
              />
              <span class="label-text text-xs">Jadikan langsung sebagai tahun pelajaran aktif</span>
            </label>
          </div>

          <!-- Actions Button -->
          <div class="modal-action pt-4 border-t border-base-200">
            <button type="button" @click="closeModal" class="btn btn-ghost btn-sm" :disabled="form.processing">
              Batal
            </button>
            <button type="submit" class="btn btn-primary btn-sm" :disabled="form.processing">
              <span v-if="form.processing" class="loading loading-spinner loading-xs"></span>
              <span>{{ modalMode === 'create' ? 'Simpan' : 'Perbarui' }}</span>
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

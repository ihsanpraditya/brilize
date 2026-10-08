<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import AppLayout from '../../../Layouts/AppLayout.vue'

interface KelasItem {
  id: number
  namaKelas: string
  tingkat: number
  jurusan?: string | null
  tahunPelajaranId?: number | null
  tahunPelajaranNama?: string | null
  waliKelasId?: number | null
  waliKelasNama?: string | null
  kapasitas: number
  keterangan?: string | null
  isActive: boolean
  createdAt: string
}

interface OptionTahun {
  id: number
  nama: string
  isActive: boolean
}

interface OptionGuru {
  id: number
  name: string
  nip?: string | null
}

interface Props {
  kelasList: KelasItem[]
  options: {
    tahunPelajaranOptions: OptionTahun[]
    activeTahunPelajaranId?: number | null
    guruOptions: OptionGuru[]
  }
  filters: {
    q?: string
    tahun_pelajaran_id?: number | null
    tingkat?: number | null
  }
}

const props = defineProps<Props>()

const search = ref(props.filters.q ?? '')
const selectedTahun = ref(props.filters.tahun_pelajaran_id ? String(props.filters.tahun_pelajaran_id) : '')
const selectedTingkat = ref(props.filters.tingkat ? String(props.filters.tingkat) : '')

const isModalOpen = ref(false)
const modalMode = ref<'create' | 'edit'>('create')
const editingId = ref<number | null>(null)

const form = useForm({
  nama_kelas: '',
  tingkat: 10,
  jurusan: '',
  tahun_pelajaran_id: props.options.activeTahunPelajaranId ?? '',
  wali_kelas_id: '',
  kapasitas: 36,
  keterangan: '',
  is_active: true,
})

// Filter watcher
function applyFilter() {
  router.get('/akademik/kelas', {
    q: search.value || undefined,
    tahun_pelajaran_id: selectedTahun.value || undefined,
    tingkat: selectedTingkat.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  })
}

watch([selectedTahun, selectedTingkat], () => {
  applyFilter()
})

let searchTimeout: any = null
watch(search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilter()
  }, 400)
})

function openCreateModal() {
  modalMode.value = 'create'
  editingId.value = null
  form.reset()
  form.clearErrors()
  form.tingkat = 10
  form.kapasitas = 36
  form.is_active = true
  form.tahun_pelajaran_id = props.options.activeTahunPelajaranId ?? ''
  isModalOpen.value = true
}

function openEditModal(item: KelasItem) {
  modalMode.value = 'edit'
  editingId.value = item.id
  form.clearErrors()
  form.nama_kelas = item.namaKelas
  form.tingkat = item.tingkat
  form.jurusan = item.jurusan ?? ''
  form.tahun_pelajaran_id = item.tahunPelajaranId ? String(item.tahunPelajaranId) : ''
  form.wali_kelas_id = item.waliKelasId ? String(item.waliKelasId) : ''
  form.kapasitas = item.kapasitas
  form.keterangan = item.keterangan ?? ''
  form.is_active = item.isActive
  isModalOpen.value = true
}

function closeModal() {
  isModalOpen.value = false
  form.reset()
}

function submit() {
  if (modalMode.value === 'create') {
    form.post('/akademik/kelas', {
      onSuccess: () => closeModal(),
    })
  } else if (editingId.value !== null) {
    form.put(`/akademik/kelas/${editingId.value}`, {
      onSuccess: () => closeModal(),
    })
  }
}

function hapus(item: KelasItem) {
  if (confirm(`Apakah Anda yakin ingin menghapus data Kelas "${item.namaKelas}"?`)) {
    router.delete(`/akademik/kelas/${item.id}`, {
      preserveScroll: true,
    })
  }
}
</script>

<template>
  <AppLayout>
    <Head title="Data Kelas & Rombel - ERP Sekolah" />

    <div class="space-y-6">
      
      <!-- Header Section -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-base-content">Data Kelas & Rombongan Belajar</h1>
          <p class="text-sm opacity-70">Manajemen master kelas, alokasi tingkat, jurusan, dan penugasan wali kelas.</p>
        </div>
        <div>
          <button @click="openCreateModal" class="btn btn-primary btn-sm md:btn-md shadow-sm">
            + Tambah Kelas Baru
          </button>
        </div>
      </div>

      <!-- Filter & Search Bar -->
      <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
          
          <!-- Search Input -->
          <div class="form-control">
            <input
              v-model="search"
              type="text"
              placeholder="Cari nama kelas / wali..."
              class="input input-bordered input-sm w-full"
            />
          </div>

          <!-- Filter Tingkat -->
          <div class="form-control">
            <select v-model="selectedTingkat" class="select select-bordered select-sm w-full">
              <option value="">Semua Tingkat</option>
              <option value="10">Tingkat 10 (Kelas X)</option>
              <option value="11">Tingkat 11 (Kelas XI)</option>
              <option value="12">Tingkat 12 (Kelas XII)</option>
              <option value="7">Tingkat 7 (Kelas VII)</option>
              <option value="8">Tingkat 8 (Kelas VIII)</option>
              <option value="9">Tingkat 9 (Kelas IX)</option>
            </select>
          </div>

          <!-- Filter Tahun Pelajaran -->
          <div class="form-control">
            <select v-model="selectedTahun" class="select select-bordered select-sm w-full">
              <option value="">Semua Tahun Pelajaran</option>
              <option
                v-for="tp in props.options.tahunPelajaranOptions"
                :key="tp.id"
                :value="String(tp.id)"
              >
                {{ tp.nama }} {{ tp.isActive ? '(Aktif)' : '' }}
              </option>
            </select>
          </div>

          <!-- Reset Filter Button -->
          <div class="flex items-center">
            <button
              v-if="search || selectedTahun || selectedTingkat"
              @click="search = ''; selectedTahun = ''; selectedTingkat = '';"
              class="btn btn-ghost btn-sm text-xs opacity-70 hover:opacity-100"
            >
              ✕ Reset Filter
            </button>
          </div>

        </div>
      </div>

      <!-- Tabel Daftar Kelas -->
      <div class="card bg-base-100 shadow border border-base-200">
        <div class="card-body p-0">
          <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
              <thead>
                <tr class="text-xs uppercase text-base-content/60 bg-base-200/50">
                  <th>Nama Kelas</th>
                  <th>Tingkat & Jurusan</th>
                  <th>Wali Kelas</th>
                  <th>Tahun Pelajaran</th>
                  <th>Kapasitas</th>
                  <th>Status</th>
                  <th class="text-right">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in props.kelasList" :key="item.id" class="hover">
                  <td>
                    <div class="font-bold text-base text-primary">{{ item.namaKelas }}</div>
                    <div v-if="item.keterangan" class="text-[11px] opacity-50">{{ item.keterangan }}</div>
                  </td>
                  <td>
                    <div class="flex items-center gap-1.5">
                      <span class="badge badge-neutral badge-sm font-bold">Tk. {{ item.tingkat }}</span>
                      <span v-if="item.jurusan" class="badge badge-outline badge-sm">{{ item.jurusan }}</span>
                    </div>
                  </td>
                  <td>
                    <div v-if="item.waliKelasNama" class="flex items-center gap-2">
                      <div class="avatar placeholder">
                        <div class="bg-primary/10 text-primary rounded-full w-6 h-6 text-xs font-bold">
                          {{ item.waliKelasNama.charAt(0) }}
                        </div>
                      </div>
                      <span class="font-medium text-xs">{{ item.waliKelasNama }}</span>
                    </div>
                    <span v-else class="text-xs opacity-40 italic">Belum ditugaskan</span>
                  </td>
                  <td>
                    <span class="text-xs font-medium">{{ item.tahunPelajaranNama ?? '-' }}</span>
                  </td>
                  <td>
                    <span class="text-xs font-mono font-semibold">{{ item.kapasitas }} Siswa</span>
                  </td>
                  <td>
                    <span
                      class="badge badge-sm font-semibold"
                      :class="item.isActive ? 'badge-success' : 'badge-neutral opacity-60'"
                    >
                      {{ item.isActive ? 'Aktif' : 'Nonaktif' }}
                    </span>
                  </td>
                  <td class="text-right space-x-2">
                    <button
                      @click="openEditModal(item)"
                      class="btn btn-ghost btn-xs text-primary"
                    >
                      Edit
                    </button>
                    <button
                      @click="hapus(item)"
                      class="btn btn-ghost btn-xs text-error"
                    >
                      Hapus
                    </button>
                  </td>
                </tr>

                <tr v-if="props.kelasList.length === 0">
                  <td colspan="7" class="text-center py-12 opacity-60">
                    Belum ada data kelas yang sesuai dengan filter. Klik tombol <b>+ Tambah Kelas Baru</b> untuk menambahkan.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>

    <!-- Modal Form Tambah / Edit Kelas -->
    <dialog class="modal" :class="{ 'modal-open': isModalOpen }">
      <div class="modal-box max-w-lg">
        <h3 class="font-bold text-lg text-base-content mb-4">
          {{ modalMode === 'create' ? 'Tambah Kelas Baru' : 'Edit Data Kelas' }}
        </h3>

        <form @submit.prevent="submit" class="space-y-4">
          
          <!-- Nama Kelas -->
          <div class="form-control w-full">
            <label class="label pb-1">
              <span class="label-text font-semibold text-xs uppercase">Nama Kelas / Rombel *</span>
              <span class="label-text-alt opacity-50">Contoh: X-RPL-1, XI-IPA-2</span>
            </label>
            <input
              v-model="form.nama_kelas"
              type="text"
              placeholder="Contoh: X-RPL-1"
              class="input input-bordered w-full"
              :class="{ 'input-error': form.errors.nama_kelas }"
              required
            />
            <div v-if="form.errors.nama_kelas" class="text-error text-xs mt-1">{{ form.errors.nama_kelas }}</div>
          </div>

          <!-- Tingkat & Kapasitas -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="form-control w-full">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase">Tingkat Jenjang *</span>
              </label>
              <input
                v-model.number="form.tingkat"
                type="number"
                min="1"
                max="13"
                class="input input-bordered w-full"
                required
              />
            </div>
            <div class="form-control w-full">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase">Kapasitas Maksimal</span>
              </label>
              <input
                v-model.number="form.kapasitas"
                type="number"
                min="1"
                max="100"
                class="input input-bordered w-full"
              />
            </div>
          </div>

          <!-- Jurusan -->
          <div class="form-control w-full">
            <label class="label pb-1">
              <span class="label-text font-semibold text-xs uppercase">Jurusan / Peminatan</span>
              <span class="label-text-alt opacity-50">Opsional</span>
            </label>
            <input
              v-model="form.jurusan"
              type="text"
              placeholder="Contoh: Rekayasa Perangkat Lunak, MIPA, IPS"
              class="input input-bordered w-full"
            />
          </div>

          <!-- Tahun Pelajaran -->
          <div class="form-control w-full">
            <label class="label pb-1">
              <span class="label-text font-semibold text-xs uppercase">Tahun Pelajaran</span>
            </label>
            <select v-model="form.tahun_pelajaran_id" class="select select-bordered w-full">
              <option value="">-- Pilih Tahun Pelajaran --</option>
              <option
                v-for="tp in props.options.tahunPelajaranOptions"
                :key="tp.id"
                :value="String(tp.id)"
              >
                {{ tp.nama }} {{ tp.isActive ? '(Aktif)' : '' }}
              </option>
            </select>
          </div>

          <!-- Wali Kelas -->
          <div class="form-control w-full">
            <label class="label pb-1">
              <span class="label-text font-semibold text-xs uppercase">Wali Kelas</span>
              <span class="label-text-alt opacity-50">Guru Pengampu</span>
            </label>
            <select v-model="form.wali_kelas_id" class="select select-bordered w-full">
              <option value="">-- Belum Ditugaskan --</option>
              <option
                v-for="guru in props.options.guruOptions"
                :key="guru.id"
                :value="String(guru.id)"
              >
                {{ guru.name }} {{ guru.nip ? '(' + guru.nip + ')' : '' }}
              </option>
            </select>
          </div>

          <!-- Keterangan -->
          <div class="form-control w-full">
            <label class="label pb-1">
              <span class="label-text font-semibold text-xs uppercase">Keterangan / Ruangan</span>
            </label>
            <input
              v-model="form.keterangan"
              type="text"
              placeholder="Contoh: Ruang 102 Gedung B"
              class="input input-bordered w-full"
            />
          </div>

          <!-- Status Aktif Toggle -->
          <div class="form-control pt-2">
            <label class="label cursor-pointer justify-start gap-3">
              <input
                v-model="form.is_active"
                type="checkbox"
                class="checkbox checkbox-primary checkbox-sm rounded"
              />
              <span class="label-text text-xs">Status kelas aktif untuk pembelajaran</span>
            </label>
          </div>

          <!-- Action Buttons -->
          <div class="modal-action pt-4 border-t border-base-200">
            <button type="button" @click="closeModal" class="btn btn-ghost btn-sm" :disabled="form.processing">
              Batal
            </button>
            <button type="submit" class="btn btn-primary btn-sm" :disabled="form.processing">
              <span v-if="form.processing" class="loading loading-spinner loading-xs"></span>
              <span>{{ modalMode === 'create' ? 'Simpan Kelas' : 'Perbarui Kelas' }}</span>
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

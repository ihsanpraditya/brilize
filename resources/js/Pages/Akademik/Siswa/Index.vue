<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import AppLayout from '../../../Layouts/AppLayout.vue'

interface SiswaItem {
  id: number
  nis: string
  nisn?: string | null
  nik?: string | null
  namaLengkap: string
  namaPanggilan?: string | null
  namaArab?: string | null
  jenisKelamin: string
  jenisKelaminLabel: string
  jenisKelaminBadge: string
  tempatLahir?: string | null
  tanggalLahir?: string | null
  agama?: string | null
  alamat?: string | null
  rt?: string | null
  rw?: string | null
  kelurahan?: string | null
  kecamatan?: string | null
  kabupatenKota?: string | null
  provinsi?: string | null
  kodePos?: string | null
  nomorHp?: string | null
  email?: string | null
  kelasId?: number | null
  kelasNama?: string | null
  kelasTingkat?: number | null
  kelasJurusan?: string | null
  status: string
  statusLabel: string
  statusBadge: string
  namaAyah?: string | null
  pekerjaanAyah?: string | null
  penghasilanAyah?: string | null
  noHpAyah?: string | null
  namaIbu?: string | null
  pekerjaanIbu?: string | null
  penghasilanIbu?: string | null
  noHpIbu?: string | null
  namaWali?: string | null
  hubunganWali?: string | null
  pekerjaanWali?: string | null
  noHpWali?: string | null
  alamatOrangTua?: string | null
  anakKe?: number | null
  jumlahSaudara?: number | null
  tanggalMasuk?: string | null
  asalSekolah?: string | null
  fotoUrl?: string | null
  catatan?: string | null
  isActive: boolean
  userId?: number | null
  userUsername?: string | null
  createdAt: string
}

interface KelasOption {
  id: number
  namaKelas: string
  tingkat: number
  jurusan?: string | null
}

interface EnumOption {
  value: string
  label: string
  badge?: string
}

interface Props {
  siswaList: SiswaItem[]
  options: {
    kelas: KelasOption[]
    jenisKelamin: EnumOption[]
    statusSiswa: EnumOption[]
    agama: string[]
  }
  statistics: {
    total: number
    aktif: number
    lulus: number
    mutasi: number
    keluar: number
    nonaktif: number
  }
  filters: {
    q?: string | null
    kelas_id?: number | null
    status?: string | null
    jenis_kelamin?: string | null
    is_active?: string | null
  }
}

const props = defineProps<Props>()

const search = ref(props.filters.q ?? '')
const selectedKelas = ref(props.filters.kelas_id ? String(props.filters.kelas_id) : '')
const selectedStatus = ref(props.filters.status ?? '')
const selectedJk = ref(props.filters.jenis_kelamin ?? '')

const isModalOpen = ref(false)
const modalMode = ref<'create' | 'edit'>('create')
const editingId = ref<number | null>(null)
const activeTab = ref<'biodata' | 'ortu' | 'lainnya'>('biodata')

// Detail Modal
const isDetailOpen = ref(false)
const selectedSiswa = ref<SiswaItem | null>(null)

const form = useForm({
  nis: '',
  nisn: '',
  nik: '',
  nama_lengkap: '',
  nama_panggilan: '',
  nama_arab: '',
  jenis_kelamin: 'L',
  tempat_lahir: '',
  tanggal_lahir: '',
  agama: 'Islam',
  alamat: '',
  rt: '',
  rw: '',
  kelurahan: '',
  kecamatan: '',
  kabupaten_kota: '',
  provinsi: '',
  kode_pos: '',
  nomor_hp: '',
  email: '',
  kelas_id: '' as string | number,
  status: 'aktif',
  nama_ayah: '',
  pekerjaan_ayah: '',
  penghasilan_ayah: '',
  no_hp_ayah: '',
  nama_ibu: '',
  pekerjaan_ibu: '',
  penghasilan_ibu: '',
  no_hp_ibu: '',
  nama_wali: '',
  hubungan_wali: '',
  pekerjaan_wali: '',
  no_hp_wali: '',
  alamat_orang_tua: '',
  anak_ke: '' as string | number,
  jumlah_saudara: '' as string | number,
  tanggal_masuk: '',
  asal_sekolah: '',
  foto_url: '',
  catatan: '',
  is_active: true,
  create_user_account: true,
})

// Search & Filter Watcher
watch([search, selectedKelas, selectedStatus, selectedJk], ([q, k, s, jk]) => {
  router.get(
    '/akademik/siswa',
    {
      q: q || undefined,
      kelas_id: k || undefined,
      status: s || undefined,
      jenis_kelamin: jk || undefined,
    },
    {
      preserveState: true,
      replace: true,
      preserveScroll: true,
    }
  )
})

function resetFilter() {
  search.value = ''
  selectedKelas.value = ''
  selectedStatus.value = ''
  selectedJk.value = ''
}

function openCreateModal() {
  modalMode.value = 'create'
  editingId.value = null
  activeTab.value = 'biodata'
  form.reset()
  form.clearErrors()
  form.jenis_kelamin = 'L'
  form.status = 'aktif'
  form.agama = 'Islam'
  form.is_active = true
  form.create_user_account = true
  isModalOpen.value = true
}

function openEditModal(item: SiswaItem) {
  modalMode.value = 'edit'
  editingId.value = item.id
  activeTab.value = 'biodata'
  form.clearErrors()

  form.nis = item.nis
  form.nisn = item.nisn ?? ''
  form.nik = item.nik ?? ''
  form.nama_lengkap = item.namaLengkap
  form.nama_panggilan = item.namaPanggilan ?? ''
  form.nama_arab = item.namaArab ?? ''
  form.jenis_kelamin = item.jenisKelamin
  form.tempat_lahir = item.tempatLahir ?? ''
  form.tanggal_lahir = item.tanggalLahir ?? ''
  form.agama = item.agama ?? 'Islam'
  form.alamat = item.alamat ?? ''
  form.rt = item.rt ?? ''
  form.rw = item.rw ?? ''
  form.kelurahan = item.kelurahan ?? ''
  form.kecamatan = item.kecamatan ?? ''
  form.kabupaten_kota = item.kabupatenKota ?? ''
  form.provinsi = item.provinsi ?? ''
  form.kode_pos = item.kodePos ?? ''
  form.nomorHp = item.nomorHp ?? ''
  form.email = item.email ?? ''
  form.kelas_id = item.kelasId ? String(item.kelasId) : ''
  form.status = item.status
  form.nama_ayah = item.namaAyah ?? ''
  form.pekerjaan_ayah = item.pekerjaanAyah ?? ''
  form.penghasilan_ayah = item.penghasilanAyah ?? ''
  form.no_hp_ayah = item.noHpAyah ?? ''
  form.nama_ibu = item.namaIbu ?? ''
  form.pekerjaan_ibu = item.pekerjaanIbu ?? ''
  form.penghasilan_ibu = item.penghasilanIbu ?? ''
  form.no_hp_ibu = item.noHpIbu ?? ''
  form.nama_wali = item.namaWali ?? ''
  form.hubungan_wali = item.hubunganWali ?? ''
  form.pekerjaan_wali = item.pekerjaanWali ?? ''
  form.no_hp_wali = item.noHpWali ?? ''
  form.alamat_orang_tua = item.alamatOrangTua ?? ''
  form.anak_ke = item.anakKe ?? ''
  form.jumlah_saudara = item.jumlahSaudara ?? ''
  form.tanggal_masuk = item.tanggalMasuk ?? ''
  form.asal_sekolah = item.asalSekolah ?? ''
  form.foto_url = item.fotoUrl ?? ''
  form.catatan = item.catatan ?? ''
  form.is_active = item.isActive

  isModalOpen.value = true
}

function openDetailModal(item: SiswaItem) {
  selectedSiswa.value = item
  isDetailOpen.value = true
}

function submitForm() {
  if (modalMode.value === 'create') {
    form.post('/akademik/siswa', {
      preserveScroll: true,
      onSuccess: () => {
        isModalOpen.value = false
        form.reset()
      },
    })
  } else if (editingId.value) {
    form.put(`/akademik/siswa/${editingId.value}`, {
      preserveScroll: true,
      onSuccess: () => {
        isModalOpen.value = false
        form.reset()
      },
    })
  }
}

function toggleStatus(item: SiswaItem) {
  router.post(`/akademik/siswa/${item.id}/toggle-status`, {}, { preserveScroll: true })
}

function deleteSiswa(item: SiswaItem) {
  if (confirm(`Apakah Anda yakin ingin menghapus data siswa "${item.namaLengkap}" (${item.nis})?`)) {
    router.delete(`/akademik/siswa/${item.id}`, {
      preserveScroll: true,
    })
  }
}
</script>

<template>
  <AppLayout>
    <Head title="Data Siswa - Portal Akademik" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-base-content">Direktori Siswa</h1>
          <p class="text-sm opacity-70">Kelola buku induk siswa, data orang tua/wali, dan penempatan kelas rombel.</p>
        </div>
        <div class="flex items-center gap-2">
          <button @click="openCreateModal" class="btn btn-primary btn-sm shadow-sm gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Siswa
          </button>
        </div>
      </div>

      <!-- Statistik Counter Cards -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <div class="card bg-base-100 shadow-sm border border-base-200 p-4">
          <div class="text-xs uppercase font-semibold text-base-content/60">Total Siswa</div>
          <div class="text-2xl font-bold text-primary mt-1">{{ props.statistics.total }}</div>
        </div>
        <div class="card bg-base-100 shadow-sm border border-base-200 p-4">
          <div class="text-xs uppercase font-semibold text-base-content/60">Siswa Aktif</div>
          <div class="text-2xl font-bold text-success mt-1">{{ props.statistics.aktif }}</div>
        </div>
        <div class="card bg-base-100 shadow-sm border border-base-200 p-4">
          <div class="text-xs uppercase font-semibold text-base-content/60">Alumni / Lulus</div>
          <div class="text-2xl font-bold text-info mt-1">{{ props.statistics.lulus }}</div>
        </div>
        <div class="card bg-base-100 shadow-sm border border-base-200 p-4">
          <div class="text-xs uppercase font-semibold text-base-content/60">Mutasi / Pindah</div>
          <div class="text-2xl font-bold text-warning mt-1">{{ props.statistics.mutasi }}</div>
        </div>
        <div class="card bg-base-100 shadow-sm border border-base-200 p-4">
          <div class="text-xs uppercase font-semibold text-base-content/60">Keluar / DO</div>
          <div class="text-2xl font-bold text-error mt-1">{{ props.statistics.keluar }}</div>
        </div>
      </div>

      <!-- Filter Bar -->
      <div class="card bg-base-100 shadow border border-base-200 p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 items-end">
          <div class="form-control">
            <label class="label py-1"><span class="label-text text-xs">Pencarian</span></label>
            <input
              v-model="search"
              type="text"
              placeholder="Cari NIS, Nama, NISN..."
              class="input input-bordered input-sm w-full"
            />
          </div>

          <div class="form-control">
            <label class="label py-1"><span class="label-text text-xs">Kelas / Rombel</span></label>
            <select v-model="selectedKelas" class="select select-bordered select-sm w-full">
              <option value="">Semua Kelas</option>
              <option v-for="k in props.options.kelas" :key="k.id" :value="k.id">
                {{ k.namaKelas }} (Tingkat {{ k.tingkat }})
              </option>
            </select>
          </div>

          <div class="form-control">
            <label class="label py-1"><span class="label-text text-xs">Status Kesiswaan</span></label>
            <select v-model="selectedStatus" class="select select-bordered select-sm w-full">
              <option value="">Semua Status</option>
              <option v-for="s in props.options.statusSiswa" :key="s.value" :value="s.value">
                {{ s.label }}
              </option>
            </select>
          </div>

          <div class="form-control">
            <label class="label py-1"><span class="label-text text-xs">Jenis Kelamin</span></label>
            <select v-model="selectedJk" class="select select-bordered select-sm w-full">
              <option value="">Semua L/P</option>
              <option v-for="jk in props.options.jenisKelamin" :key="jk.value" :value="jk.value">
                {{ jk.label }}
              </option>
            </select>
          </div>

          <div>
            <button @click="resetFilter" class="btn btn-ghost btn-sm w-full border-base-300">
              Reset Filter
            </button>
          </div>
        </div>
      </div>

      <!-- Tabel Siswa -->
      <div class="card bg-base-100 shadow border border-base-200">
        <div class="overflow-x-auto">
          <table class="table table-zebra w-full text-sm">
            <thead>
              <tr class="bg-base-200/50">
                <th class="w-12 text-center">#</th>
                <th>NIS / NISN</th>
                <th>Nama Siswa</th>
                <th>Nama Arab</th>
                <th>L/P</th>
                <th>Kelas</th>
                <th>Status</th>
                <th class="text-right">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, idx) in props.siswaList" :key="item.id" class="hover">
                <td class="text-center font-mono text-xs opacity-60">{{ idx + 1 }}</td>
                <td>
                  <div class="font-bold text-primary font-mono">{{ item.nis }}</div>
                  <div class="text-xs opacity-60 font-mono">NISN: {{ item.nisn ?? '-' }}</div>
                </td>
                <td>
                  <div class="font-semibold text-base-content">{{ item.namaLengkap }}</div>
                  <div v-if="item.namaPanggilan" class="text-xs opacity-60">({{ item.namaPanggilan }})</div>
                </td>
                <td>
                  <span v-if="item.namaArab" class="font-serif text-base text-secondary font-medium" dir="rtl">
                    {{ item.namaArab }}
                  </span>
                  <span v-else class="text-xs opacity-40">-</span>
                </td>
                <td>
                  <span class="badge badge-sm font-semibold" :class="item.jenisKelaminBadge">
                    {{ item.jenisKelaminLabel }}
                  </span>
                </td>
                <td>
                  <div v-if="item.kelasNama" class="badge badge-outline badge-primary font-medium badge-sm">
                    {{ item.kelasNama }}
                  </div>
                  <span v-else class="text-xs opacity-50 italic">Belum ditentukan</span>
                </td>
                <td>
                  <span class="badge badge-sm" :class="item.statusBadge">
                    {{ item.statusLabel }}
                  </span>
                </td>
                <td class="text-right space-x-1">
                  <button @click="openDetailModal(item)" class="btn btn-ghost btn-xs text-info" title="Lihat Profil Lengkap">
                    Detail
                  </button>
                  <button @click="openEditModal(item)" class="btn btn-ghost btn-xs text-warning" title="Edit Data">
                    Edit
                  </button>
                  <button @click="deleteSiswa(item)" class="btn btn-ghost btn-xs text-error" title="Hapus Data">
                    Hapus
                  </button>
                </td>
              </tr>

              <tr v-if="props.siswaList.length === 0">
                <td colspan="8" class="text-center py-12 opacity-60">
                  <div class="flex flex-col items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Tidak ada data siswa yang ditemukan.</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal Form Tambah / Edit Siswa -->
    <dialog class="modal" :class="{ 'modal-open': isModalOpen }">
      <div class="modal-box max-w-4xl max-h-[90vh] overflow-y-auto">
        <h3 class="font-bold text-lg text-base-content border-b border-base-200 pb-3">
          {{ modalMode === 'create' ? 'Tambah Siswa Baru' : 'Edit Biodata Siswa' }}
        </h3>

        <!-- Tabs Navigasi Form -->
        <div class="tabs tabs-boxed my-4 bg-base-200">
          <a
            class="tab"
            :class="{ 'tab-active': activeTab === 'biodata' }"
            @click="activeTab = 'biodata'"
          >
            1. Biodata Siswa
          </a>
          <a
            class="tab"
            :class="{ 'tab-active': activeTab === 'ortu' }"
            @click="activeTab = 'ortu'"
          >
            2. Orang Tua & Wali
          </a>
          <a
            class="tab"
            :class="{ 'tab-active': activeTab === 'lainnya' }"
            @click="activeTab = 'lainnya'"
          >
            3. Dapodik & Akun Portal
          </a>
        </div>

        <form @submit.prevent="submitForm" class="space-y-4">
          <!-- TAB 1: BIODATA SISWA -->
          <div v-show="activeTab === 'biodata'" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
              <div class="form-control">
                <label class="label py-1"><span class="label-text text-xs font-semibold">NIS (Lokal) <span class="text-error">*</span></span></label>
                <input v-model="form.nis" type="text" placeholder="Contoh: 20261001" class="input input-bordered input-sm" required />
                <span v-if="form.errors.nis" class="text-error text-xs mt-1">{{ form.errors.nis }}</span>
              </div>

              <div class="form-control">
                <label class="label py-1"><span class="label-text text-xs">NISN (Dapodik)</span></label>
                <input v-model="form.nisn" type="text" placeholder="10 Digit NISN" class="input input-bordered input-sm" />
                <span v-if="form.errors.nisn" class="text-error text-xs mt-1">{{ form.errors.nisn }}</span>
              </div>

              <div class="form-control">
                <label class="label py-1"><span class="label-text text-xs">NIK (KTP / KK)</span></label>
                <input v-model="form.nik" type="text" placeholder="16 Digit NIK" class="input input-bordered input-sm" />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
              <div class="form-control md:col-span-2">
                <label class="label py-1"><span class="label-text text-xs font-semibold">Nama Lengkap <span class="text-error">*</span></span></label>
                <input v-model="form.nama_lengkap" type="text" placeholder="Nama lengkap sesuai akta lahir" class="input input-bordered input-sm" required />
                <span v-if="form.errors.nama_lengkap" class="text-error text-xs mt-1">{{ form.errors.nama_lengkap }}</span>
              </div>

              <div class="form-control">
                <label class="label py-1"><span class="label-text text-xs">Nama Panggilan</span></label>
                <input v-model="form.nama_panggilan" type="text" placeholder="Nama panggilan" class="input input-bordered input-sm" />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
              <div class="form-control">
                <label class="label py-1"><span class="label-text text-xs">Nama Arab (Pesantren/Madrasah)</span></label>
                <input v-model="form.nama_arab" type="text" placeholder="contoh: محمّد علي" class="input input-bordered input-sm font-serif" dir="rtl" />
              </div>

              <div class="form-control">
                <label class="label py-1"><span class="label-text text-xs font-semibold">Jenis Kelamin</span></label>
                <select v-model="form.jenis_kelamin" class="select select-bordered select-sm">
                  <option v-for="jk in props.options.jenisKelamin" :key="jk.value" :value="jk.value">
                    {{ jk.label }}
                  </option>
                </select>
              </div>

              <div class="form-control">
                <label class="label py-1"><span class="label-text text-xs">Agama</span></label>
                <select v-model="form.agama" class="select select-bordered select-sm">
                  <option v-for="ag in props.options.agama" :key="ag" :value="ag">{{ ag }}</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div class="form-control">
                <label class="label py-1"><span class="label-text text-xs">Tempat Lahir</span></label>
                <input v-model="form.tempat_lahir" type="text" placeholder="Kota / Kabupaten Lahir" class="input input-bordered input-sm" />
              </div>

              <div class="form-control">
                <label class="label py-1"><span class="label-text text-xs">Tanggal Lahir</span></label>
                <input v-model="form.tanggal_lahir" type="date" class="input input-bordered input-sm" />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div class="form-control">
                <label class="label py-1"><span class="label-text text-xs">Kelas / Rombel</span></label>
                <select v-model="form.kelas_id" class="select select-bordered select-sm">
                  <option value="">Pilih Kelas</option>
                  <option v-for="k in props.options.kelas" :key="k.id" :value="k.id">
                    {{ k.namaKelas }} (Tingkat {{ k.tingkat }})
                  </option>
                </select>
              </div>

              <div class="form-control">
                <label class="label py-1"><span class="label-text text-xs font-semibold">Status Kesiswaan</span></label>
                <select v-model="form.status" class="select select-bordered select-sm">
                  <option v-for="st in props.options.statusSiswa" :key="st.value" :value="st.value">
                    {{ st.label }}
                  </option>
                </select>
              </div>
            </div>

            <div class="form-control">
              <label class="label py-1"><span class="label-text text-xs">Alamat Tinggal</span></label>
              <textarea v-model="form.alamat" rows="2" placeholder="Jalan, Gang, Dusun..." class="textarea textarea-bordered textarea-sm"></textarea>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
              <div class="form-control">
                <label class="label py-1"><span class="label-text text-xs">RT / RW</span></label>
                <div class="flex gap-1">
                  <input v-model="form.rt" type="text" placeholder="RT" class="input input-bordered input-sm w-1/2" />
                  <input v-model="form.rw" type="text" placeholder="RW" class="input input-bordered input-sm w-1/2" />
                </div>
              </div>
              <div class="form-control">
                <label class="label py-1"><span class="label-text text-xs">Kelurahan / Desa</span></label>
                <input v-model="form.kelurahan" type="text" placeholder="Kelurahan" class="input input-bordered input-sm" />
              </div>
              <div class="form-control">
                <label class="label py-1"><span class="label-text text-xs">Kecamatan</span></label>
                <input v-model="form.kecamatan" type="text" placeholder="Kecamatan" class="input input-bordered input-sm" />
              </div>
              <div class="form-control">
                <label class="label py-1"><span class="label-text text-xs">Kabupaten / Kota</span></label>
                <input v-model="form.kabupaten_kota" type="text" placeholder="Kota" class="input input-bordered input-sm" />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div class="form-control">
                <label class="label py-1"><span class="label-text text-xs">No. HP Siswa</span></label>
                <input v-model="form.nomor_hp" type="text" placeholder="08xxxxxxxxxx" class="input input-bordered input-sm" />
              </div>
              <div class="form-control">
                <label class="label py-1"><span class="label-text text-xs">Email Siswa</span></label>
                <input v-model="form.email" type="email" placeholder="siswa@sekolah.sch.id" class="input input-bordered input-sm" />
              </div>
            </div>
          </div>

          <!-- TAB 2: DATA ORANG TUA & WALI -->
          <div v-show="activeTab === 'ortu'" class="space-y-4">
            <div class="p-3 bg-base-200/50 rounded-box space-y-3">
              <h4 class="font-bold text-xs uppercase tracking-wider text-primary">Data Ayah Kandung</h4>
              <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div class="form-control md:col-span-2">
                  <label class="label py-1"><span class="label-text text-xs">Nama Ayah</span></label>
                  <input v-model="form.nama_ayah" type="text" placeholder="Nama lengkap ayah" class="input input-bordered input-sm" />
                </div>
                <div class="form-control">
                  <label class="label py-1"><span class="label-text text-xs">Pekerjaan Ayah</span></label>
                  <input v-model="form.pekerjaan_ayah" type="text" placeholder="PNS, Wiraswasta, Petani..." class="input input-bordered input-sm" />
                </div>
                <div class="form-control">
                  <label class="label py-1"><span class="label-text text-xs">No. HP Ayah</span></label>
                  <input v-model="form.no_hp_ayah" type="text" placeholder="08xxxxxxxxxx" class="input input-bordered input-sm" />
                </div>
              </div>
            </div>

            <div class="p-3 bg-base-200/50 rounded-box space-y-3">
              <h4 class="font-bold text-xs uppercase tracking-wider text-secondary">Data Ibu Kandung</h4>
              <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div class="form-control md:col-span-2">
                  <label class="label py-1"><span class="label-text text-xs">Nama Ibu</span></label>
                  <input v-model="form.nama_ibu" type="text" placeholder="Nama lengkap ibu" class="input input-bordered input-sm" />
                </div>
                <div class="form-control">
                  <label class="label py-1"><span class="label-text text-xs">Pekerjaan Ibu</span></label>
                  <input v-model="form.pekerjaan_ibu" type="text" placeholder="IRT, Guru, Pedagang..." class="input input-bordered input-sm" />
                </div>
                <div class="form-control">
                  <label class="label py-1"><span class="label-text text-xs">No. HP Ibu</span></label>
                  <input v-model="form.no_hp_ibu" type="text" placeholder="08xxxxxxxxxx" class="input input-bordered input-sm" />
                </div>
              </div>
            </div>

            <div class="p-3 bg-base-200/50 rounded-box space-y-3">
              <h4 class="font-bold text-xs uppercase tracking-wider text-base-content/80">Data Wali (Opsional)</h4>
              <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div class="form-control">
                  <label class="label py-1"><span class="label-text text-xs">Nama Wali</span></label>
                  <input v-model="form.nama_wali" type="text" placeholder="Nama wali" class="input input-bordered input-sm" />
                </div>
                <div class="form-control">
                  <label class="label py-1"><span class="label-text text-xs">Hubungan Wali</span></label>
                  <input v-model="form.hubungan_wali" type="text" placeholder="Kakek, Paman, Kakak..." class="input input-bordered input-sm" />
                </div>
                <div class="form-control">
                  <label class="label py-1"><span class="label-text text-xs">Pekerjaan Wali</span></label>
                  <input v-model="form.pekerjaan_wali" type="text" placeholder="Pekerjaan wali" class="input input-bordered input-sm" />
                </div>
                <div class="form-control">
                  <label class="label py-1"><span class="label-text text-xs">No. HP Wali</span></label>
                  <input v-model="form.no_hp_wali" type="text" placeholder="08xxxxxxxxxx" class="input input-bordered input-sm" />
                </div>
              </div>
            </div>

            <div class="form-control">
              <label class="label py-1"><span class="label-text text-xs">Alamat Orang Tua / Wali</span></label>
              <textarea v-model="form.alamat_orang_tua" rows="2" placeholder="Alamat domisili orang tua jika berbeda" class="textarea textarea-bordered textarea-sm"></textarea>
            </div>
          </div>

          <!-- TAB 3: DATA TAMBAHAN & AKUN PORTAL -->
          <div v-show="activeTab === 'lainnya'" class="space-y-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
              <div class="form-control">
                <label class="label py-1"><span class="label-text text-xs">Anak Ke-</span></label>
                <input v-model="form.anak_ke" type="number" min="1" placeholder="1" class="input input-bordered input-sm" />
              </div>
              <div class="form-control">
                <label class="label py-1"><span class="label-text text-xs">Jumlah Saudara</span></label>
                <input v-model="form.jumlah_saudara" type="number" min="0" placeholder="0" class="input input-bordered input-sm" />
              </div>
              <div class="form-control">
                <label class="label py-1"><span class="label-text text-xs">Tanggal Masuk</span></label>
                <input v-model="form.tanggal_masuk" type="date" class="input input-bordered input-sm" />
              </div>
              <div class="form-control">
                <label class="label py-1"><span class="label-text text-xs">Asal Sekolah</span></label>
                <input v-model="form.asal_sekolah" type="text" placeholder="SMPN 1 / MTs..." class="input input-bordered input-sm" />
              </div>
            </div>

            <div class="form-control">
              <label class="label py-1"><span class="label-text text-xs">Foto Profil URL</span></label>
              <input v-model="form.foto_url" type="text" placeholder="https://..." class="input input-bordered input-sm" />
            </div>

            <div class="form-control">
              <label class="label py-1"><span class="label-text text-xs">Catatan Tambahan / BK</span></label>
              <textarea v-model="form.catatan" rows="2" placeholder="Prestasi, catatan khusus, beasiswa..." class="textarea textarea-bordered textarea-sm"></textarea>
            </div>

            <div v-if="modalMode === 'create'" class="p-3 bg-primary/10 rounded-box border border-primary/20 space-y-2">
              <label class="cursor-pointer label justify-start gap-3">
                <input v-model="form.create_user_account" type="checkbox" class="checkbox checkbox-primary checkbox-sm" />
                <div>
                  <div class="font-bold text-xs text-primary">Buat Akun Portal Siswa Otomatis</div>
                  <div class="text-xs opacity-70">Sistem akan membuat akun User login dengan Username & Password default menggunakan NIS.</div>
                </div>
              </label>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="modal-action border-t border-base-200 pt-3">
            <button type="button" @click="isModalOpen = false" class="btn btn-ghost btn-sm">Batal</button>
            <button type="submit" class="btn btn-primary btn-sm" :disabled="form.processing">
              {{ form.processing ? 'Menyimpan...' : 'Simpan Data Siswa' }}
            </button>
          </div>
        </form>
      </div>
    </dialog>

    <!-- Modal Profil Lengkap Siswa -->
    <dialog class="modal" :class="{ 'modal-open': isDetailOpen }">
      <div v-if="selectedSiswa" class="modal-box max-w-3xl">
        <div class="flex items-start justify-between border-b border-base-200 pb-3">
          <div class="flex items-center gap-3">
            <div class="avatar placeholder">
              <div class="bg-primary text-primary-content rounded-full w-12 h-12">
                <span class="text-lg font-bold">{{ selectedSiswa.namaLengkap.charAt(0) }}</span>
              </div>
            </div>
            <div>
              <h3 class="font-bold text-lg text-base-content">{{ selectedSiswa.namaLengkap }}</h3>
              <div class="flex items-center gap-2 text-xs opacity-70">
                <span class="font-mono">NIS: {{ selectedSiswa.nis }}</span>
                <span>•</span>
                <span class="font-mono">NISN: {{ selectedSiswa.nisn ?? '-' }}</span>
                <span>•</span>
                <span class="badge badge-xs" :class="selectedSiswa.statusBadge">{{ selectedSiswa.statusLabel }}</span>
              </div>
            </div>
          </div>
          <button @click="isDetailOpen = false" class="btn btn-ghost btn-xs btn-circle">✕</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 my-4 text-xs">
          <div class="space-y-2 p-3 bg-base-200/40 rounded-box">
            <div class="font-bold uppercase text-primary tracking-wider">Biodata Akademik</div>
            <div><span class="opacity-60">Nama Arab:</span> <span class="font-serif text-sm font-semibold" dir="rtl">{{ selectedSiswa.namaArab ?? '-' }}</span></div>
            <div><span class="opacity-60">Kelas:</span> <span class="font-semibold">{{ selectedSiswa.kelasNama ?? '-' }}</span></div>
            <div><span class="opacity-60">Jenis Kelamin:</span> {{ selectedSiswa.jenisKelaminLabel }}</div>
            <div><span class="opacity-60">TTL:</span> {{ selectedSiswa.tempatLahir ?? '-' }}, {{ selectedSiswa.tanggalLahir ?? '-' }}</div>
            <div><span class="opacity-60">Agama:</span> {{ selectedSiswa.agama ?? '-' }}</div>
            <div><span class="opacity-60">No. HP:</span> {{ selectedSiswa.nomorHp ?? '-' }}</div>
            <div><span class="opacity-60">Email:</span> {{ selectedSiswa.email ?? '-' }}</div>
          </div>

          <div class="space-y-2 p-3 bg-base-200/40 rounded-box">
            <div class="font-bold uppercase text-secondary tracking-wider">Data Keluarga & Domisili</div>
            <div><span class="opacity-60">Ayah:</span> {{ selectedSiswa.namaAyah ?? '-' }} ({{ selectedSiswa.pekerjaanAyah ?? '-' }})</div>
            <div><span class="opacity-60">Ibu:</span> {{ selectedSiswa.namaIbu ?? '-' }} ({{ selectedSiswa.pekerjaanIbu ?? '-' }})</div>
            <div><span class="opacity-60">No. HP Ortu:</span> {{ selectedSiswa.noHpAyah ?? selectedSiswa.noHpIbu ?? '-' }}</div>
            <div><span class="opacity-60">Alamat:</span> {{ selectedSiswa.alamat ?? '-' }}</div>
            <div><span class="opacity-60">Kel/Kec:</span> {{ selectedSiswa.kelurahan ?? '-' }} / {{ selectedSiswa.kecamatan ?? '-' }}</div>
            <div><span class="opacity-60">Kab/Kota:</span> {{ selectedSiswa.kabupatenKota ?? '-' }} {{ selectedSiswa.kodePos ?? '' }}</div>
          </div>
        </div>

        <div class="modal-action border-t border-base-200 pt-3">
          <button @click="isDetailOpen = false" class="btn btn-primary btn-sm">Tutup</button>
        </div>
      </div>
    </dialog>
  </AppLayout>
</template>

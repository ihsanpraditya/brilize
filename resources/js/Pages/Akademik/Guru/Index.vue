<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import AppLayout from '../../../Layouts/AppLayout.vue'

interface PegawaiItem {
  id: number
  namaLengkap: string
  namaDenganGelar: string
  gelarDepan?: string | null
  gelarBelakang?: string | null
  nip?: string | null
  nuptk?: string | null
  nik?: string | null
  jenisKelamin: string
  jenisKelaminLabel: string
  jenisKelaminBadge: string
  tempatLahir?: string | null
  tanggalLahir?: string | null
  agama?: string | null
  alamat?: string | null
  nomorHp?: string | null
  email?: string | null
  kategoriPegawai: 'pendidik' | 'tendik'
  kategoriPegawaiLabel: string
  kategoriPegawaiBadge: string
  jenisPegawai: string
  jenisPegawaiLabel: string
  statusKepegawaian: string
  statusKepegawaianLabel: string
  statusKepegawaianBadge: string
  jabatan?: string | null
  pendidikanTerakhir?: string | null
  jurusanPendidikan?: string | null
  tanggalMasuk?: string | null
  fotoUrl?: string | null
  isActive: boolean
  // Data Guru
  guruId?: number | null
  bidangStudiUtama?: string | null
  isSertifikasi: boolean
  noSertifikasi?: string | null
  tugasTambahan?: string | null
  tmtPendidik?: string | null
  createdAt: string
}

interface OptionItem {
  value: string
  label: string
  badge?: string
}

interface Props {
  pegawaiList: PegawaiItem[]
  options: {
    statistik: {
      total: number
      totalPendidik: number
      totalTendik: number
      totalPNS: number
      totalPPPK: number
      totalHonorer: number
    }
    kategoriOptions: OptionItem[]
    statusOptions: OptionItem[]
    jenisOptions: OptionItem[]
    jenisKelaminOptions: OptionItem[]
  }
  filters: {
    q?: string
    kategori?: string
    jenis?: string
    status?: string
  }
}

const props = defineProps<Props>()

const search = ref(props.filters.q ?? '')
const selectedKategori = ref(props.filters.kategori ?? '')
const selectedStatus = ref(props.filters.status ?? '')

const isModalOpen = ref(false)
const modalMode = ref<'create' | 'edit'>('create')
const editingId = ref<number | null>(null)

const form = useForm({
  nama_lengkap: '',
  gelar_depan: '',
  gelar_belakang: '',
  nip: '',
  nuptk: '',
  nik: '',
  jenis_kelamin: 'L',
  tempat_lahir: '',
  tanggal_lahir: '',
  agama: 'Islam',
  alamat: '',
  nomor_hp: '',
  email: '',
  kategori_pegawai: 'pendidik',
  jenis_pegawai: 'guru',
  status_kepegawaian: 'HONORER',
  jabatan: '',
  pendidikan_terakhir: 'S1',
  jurusan_pendidikan: '',
  tanggal_masuk: '',
  is_active: true,
  // Bidang Guru
  bidang_studi_utama: '',
  is_sertifikasi: false,
  no_sertifikasi: '',
  tugas_tambahan: '',
  tmt_pendidik: '',
})

function applyFilter() {
  router.get('/akademik/guru', {
    q: search.value || undefined,
    kategori: selectedKategori.value || undefined,
    status: selectedStatus.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  })
}

watch([selectedKategori, selectedStatus], () => {
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
  form.kategori_pegawai = selectedKategori.value === 'tendik' ? 'tendik' : 'pendidik'
  form.jenis_pegawai = selectedKategori.value === 'tendik' ? 'tata_usaha' : 'guru'
  form.is_active = true
  isModalOpen.value = true
}

function openEditModal(item: PegawaiItem) {
  modalMode.value = 'edit'
  editingId.value = item.id
  form.clearErrors()
  form.nama_lengkap = item.namaLengkap
  form.gelar_depan = item.gelarDepan ?? ''
  form.gelar_belakang = item.gelarBelakang ?? ''
  form.nip = item.nip ?? ''
  form.nuptk = item.nuptk ?? ''
  form.nik = item.nik ?? ''
  form.jenis_kelamin = item.jenisKelamin
  form.tempat_lahir = item.tempatLahir ?? ''
  form.tanggal_lahir = item.tanggalLahir ?? ''
  form.agama = item.agama ?? 'Islam'
  form.alamat = item.alamat ?? ''
  form.nomor_hp = item.nomorHp ?? ''
  form.email = item.email ?? ''
  form.kategori_pegawai = item.kategoriPegawai
  form.jenis_pegawai = item.jenisPegawai
  form.status_kepegawaian = item.statusKepegawaian
  form.jabatan = item.jabatan ?? ''
  form.pendidikan_terakhir = item.pendidikanTerakhir ?? 'S1'
  form.jurusan_pendidikan = item.jurusanPendidikan ?? ''
  form.tanggal_masuk = item.tanggalMasuk ?? ''
  form.is_active = item.isActive
  // Guru
  form.bidang_studi_utama = item.bidangStudiUtama ?? ''
  form.is_sertifikasi = item.isSertifikasi
  form.no_sertifikasi = item.noSertifikasi ?? ''
  form.tugas_tambahan = item.tugasTambahan ?? ''
  form.tmt_pendidik = item.tmtPendidik ?? ''
  isModalOpen.value = true
}

function closeModal() {
  isModalOpen.value = false
  form.reset()
}

function submit() {
  if (modalMode.value === 'create') {
    form.post('/akademik/guru', {
      onSuccess: () => closeModal(),
    })
  } else if (editingId.value !== null) {
    form.put(`/akademik/guru/${editingId.value}`, {
      onSuccess: () => closeModal(),
    })
  }
}

function hapus(item: PegawaiItem) {
  if (confirm(`Apakah Anda yakin ingin menghapus data "${item.namaDenganGelar}"?`)) {
    router.delete(`/akademik/guru/${item.id}`, {
      preserveScroll: true,
    })
  }
}
</script>

<template>
  <AppLayout>
    <Head title="Data Guru & PTK - ERP Sekolah" />

    <div class="space-y-6">

      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-base-content">Direktori Guru & Pegawai (PTK)</h1>
          <p class="text-sm opacity-70">Manajemen data tenaga pendidik, staf kependidikan, nomor identitas, dan status kepegawaian.</p>
        </div>
        <div>
          <button @click="openCreateModal" class="btn btn-primary btn-sm md:btn-md shadow-sm">
            + Tambah Guru / Pegawai
          </button>
        </div>
      </div>

      <!-- Statistik Kepegawaian Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="stat bg-base-100 rounded-2xl shadow-sm border border-base-200 p-4">
          <div class="stat-title text-xs font-semibold uppercase">Total SDM Sekolah</div>
          <div class="stat-value text-primary text-2xl mt-1">{{ props.options.statistik.total }}</div>
          <div class="stat-desc text-xs mt-1">Seluruh guru & staf aktif</div>
        </div>

        <div class="stat bg-base-100 rounded-2xl shadow-sm border border-base-200 p-4">
          <div class="stat-title text-xs font-semibold uppercase">Tenaga Pendidik (Guru)</div>
          <div class="stat-value text-info text-2xl mt-1">{{ props.options.statistik.totalPendidik }}</div> <!-- teks ini kurang terbaca -->
          <div class="stat-desc text-xs mt-1">Guru Mapel & BK</div>
        </div>

        <div class="stat bg-base-100 rounded-2xl shadow-sm border border-base-200 p-4">
          <div class="stat-title text-xs font-semibold uppercase">Tenaga Kependidikan</div>
          <div class="stat-value text-secondary text-2xl mt-1">{{ props.options.statistik.totalTendik }}</div>
          <div class="stat-desc text-xs mt-1">Tata Usaha, Lab, & Staf</div>
        </div>

        <div class="stat bg-base-100 rounded-2xl shadow-sm border border-base-200 p-4">
          <div class="stat-title text-xs font-semibold uppercase">Aparatur (PNS & PPPK)</div>
          <div class="stat-value text-success text-2xl mt-1">
            {{ props.options.statistik.totalPNS + props.options.statistik.totalPPPK }}
          </div>
          <div class="stat-desc text-xs mt-1">
            {{ props.options.statistik.totalPNS }} PNS | {{ props.options.statistik.totalPPPK }} PPPK
          </div>
        </div>

      </div>

      <!-- Kategori Filter Tabs & Search Filter -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">

        <!-- Tabs -->
        <div class="tabs tabs-box bg-base-200 p-1 rounded-2xl w-fit">
          <button
            @click="selectedKategori = ''"
            class="tab text-xs font-semibold"
            :class="{ 'tab-active bg-base-100 shadow-sm text-primary': selectedKategori === '' }"
          >
            Semua SDM ({{ props.options.statistik.total }})
          </button>
          <button
            @click="selectedKategori = 'pendidik'"
            class="tab text-xs font-semibold"
            :class="{ 'tab-active bg-base-100 shadow-sm text-primary': selectedKategori === 'pendidik' }"
          >
            👨‍🏫 Guru / Pendidik ({{ props.options.statistik.totalPendidik }})
          </button>
          <button
            @click="selectedKategori = 'tendik'"
            class="tab text-xs font-semibold"
            :class="{ 'tab-active bg-base-100 shadow-sm text-primary': selectedKategori === 'tendik' }"
          >
            🏢 Staf / Tendik ({{ props.options.statistik.totalTendik }})
          </button>
        </div>

        <!-- Filter Controls -->
        <div class="flex items-center gap-3 flex-wrap">
          <select v-model="selectedStatus" class="select select-bordered select-sm text-xs">
            <option value="">Semua Status Kepegawaian</option>
            <option v-for="s in props.options.statusOptions" :key="s.value" :value="s.value">
              {{ s.label }}
            </option>
          </select>

          <input
            v-model="search"
            type="text"
            placeholder="Cari nama / NIP / NUPTK..."
            class="input input-bordered input-sm w-48 sm:w-60 text-xs"
          />
        </div>

      </div>

      <!-- Tabel Daftar Pegawai / Guru -->
      <div class="card bg-base-100 shadow border border-base-200">
        <div class="card-body p-0">
          <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
              <thead>
                <tr class="text-xs uppercase text-base-content/60 bg-base-200/50">
                  <th>Nama Lengkap & NIP/NUPTK</th>
                  <th>Kategori & Jabatan</th>
                  <th>Spesialisasi Mengajar</th>
                  <th>Status Kepegawaian</th>
                  <th>Kontak</th>
                  <th>Status</th>
                  <th class="text-right">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in props.pegawaiList" :key="item.id" class="hover">

                  <!-- Nama & Identitas -->
                  <td>
                    <div class="flex items-center gap-3">
                      <div class="avatar placeholder">
                        <div class="bg-primary/10 text-primary rounded-full w-9 h-9 font-bold text-sm shadow-sm">
                          {{ item.namaLengkap.charAt(0) }}
                        </div>
                      </div>
                      <div>
                        <div class="font-bold text-sm text-base-content">{{ item.namaDenganGelar }}</div>
                        <div class="text-[11px] opacity-60 font-mono">
                          <span v-if="item.nip">NIP. {{ item.nip }}</span>
                          <span v-else-if="item.nuptk">NUPTK. {{ item.nuptk }}</span>
                          <span v-else>Non-NIP</span>
                        </div>
                      </div>
                    </div>
                  </td>

                  <!-- Kategori & Jabatan -->
                  <td>
                    <span class="badge badge-sm font-semibold" :class="item.kategoriPegawaiBadge"> <!-- badge-secondary membuat badge ini susah dibaca di mode light -->
                      {{ item.kategoriPegawaiLabel }}
                    </span>
                    <div class="text-xs text-base-content/75 mt-0.5">
                      {{ item.jabatan ?? item.jenisPegawaiLabel }}
                    </div>
                  </td>

                  <!-- Bidang Studi & Sertifikasi (Jika Guru) -->
                  <td>
                    <div v-if="item.kategoriPegawai === 'pendidik'">
                      <div class="font-medium text-xs text-base-content">
                        {{ item.bidangStudiUtama ?? 'Guru Mata Pelajaran' }}
                      </div>
                      <div class="flex items-center gap-1.5 mt-0.5">
                        <span v-if="item.isSertifikasi" class="badge badge-success badge-xs font-semibold">
                          ✓ Tersertifikasi
                        </span>
                        <span v-if="item.tugasTambahan" class="badge badge-neutral badge-xs">
                          {{ item.tugasTambahan }}
                        </span>
                      </div>
                    </div>
                    <span v-else class="text-xs opacity-40 italic">- Tenaga Kependidikan -</span>
                  </td>

                  <!-- Status Kepegawaian -->
                  <td>
                    <span class="badge badge-sm font-semibold" :class="item.statusKepegawaianBadge">
                      {{ item.statusKepegawaian }}
                    </span>
                    <div class="text-[11px] opacity-60 mt-0.5">
                      {{ item.pendidikanTerakhir ?? '-' }}
                    </div>
                  </td>

                  <!-- Kontak -->
                  <td>
                    <div class="text-xs font-mono">{{ item.nomorHp ?? '-' }}</div>
                    <div class="text-[11px] opacity-60 truncate max-w-[150px]">{{ item.email ?? '-' }}</div>
                  </td>

                  <!-- Status Aktif -->
                  <td>
                    <span class="badge badge-sm font-semibold" :class="item.isActive ? 'badge-success' : 'badge-neutral opacity-50'">
                      {{ item.isActive ? 'Aktif' : 'Nonaktif' }}
                    </span>
                  </td>

                  <!-- Aksi -->
                  <td class="text-right space-x-2 whitespace-nowrap">
                    <button @click="openEditModal(item)" class="btn btn-ghost btn-xs text-primary">
                      Edit
                    </button>
                    <button @click="hapus(item)" class="btn btn-ghost btn-xs text-error">
                      Hapus
                    </button>
                  </td>

                </tr>

                <tr v-if="props.pegawaiList.length === 0">
                  <td colspan="7" class="text-center py-12 opacity-60">
                    Belum ada data pegawai atau guru yang sesuai filter.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>

    <!-- Modal Form Tambah / Edit Pegawai & Guru -->
    <dialog class="modal" :class="{ 'modal-open': isModalOpen }">
      <div class="modal-box max-w-2xl max-h-[90vh] overflow-y-auto">
        <h3 class="font-bold text-lg text-base-content mb-4">
          {{ modalMode === 'create' ? 'Tambah Data Guru / Pegawai' : 'Edit Data Guru / Pegawai' }}
        </h3>

        <form @submit.prevent="submit" class="space-y-4">

          <!-- Kategori Pegawai & Jenis -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-base-200/60 rounded-2xl">
            <div class="form-control">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase">Kategori Pegawai *</span>
              </label>
              <select v-model="form.kategori_pegawai" class="select select-bordered select-sm w-full">
                <option value="pendidik">Tenaga Pendidik (Guru)</option>
                <option value="tendik">Tenaga Kependidikan (Staf TU / Non-Guru)</option>
              </select>
            </div>

            <div class="form-control">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase">Jenis Penugasan *</span>
              </label>
              <select v-model="form.jenis_pegawai" class="select select-bordered select-sm w-full">
                <option v-for="j in props.options.jenisOptions" :key="j.value" :value="j.value">
                  {{ j.label }}
                </option>
              </select>
            </div>
          </div>

          <!-- Bagian Biodata Personal -->
          <div class="space-y-3 pt-2">
            <h4 class="font-bold text-xs uppercase tracking-wider text-primary border-b border-base-200 pb-1">
              1. Identitas Personal
            </h4>

            <div class="form-control w-full">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase">Nama Lengkap (Tanpa Gelar) *</span>
              </label>
              <input
                v-model="form.nama_lengkap"
                type="text"
                placeholder="Contoh: Budi Santoso"
                class="input input-bordered input-sm w-full"
                :class="{ 'input-error': form.errors.nama_lengkap }"
                required
              />
              <div v-if="form.errors.nama_lengkap" class="text-error text-xs mt-1">{{ form.errors.nama_lengkap }}</div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div class="form-control">
                <label class="label pb-1"><span class="label-text font-semibold text-xs uppercase">Gelar Depan</span></label>
                <input v-model="form.gelar_depan" type="text" placeholder="Drs. / Dr. / Prof." class="input input-bordered input-sm w-full" />
              </div>
              <div class="form-control">
                <label class="label pb-1"><span class="label-text font-semibold text-xs uppercase">Gelar Belakang</span></label>
                <input v-model="form.gelar_belakang" type="text" placeholder="S.Pd. / M.Pd. / S.Kom." class="input input-bordered input-sm w-full" />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div class="form-control">
                <label class="label pb-1"><span class="label-text font-semibold text-xs uppercase">NIP (PNS/PPPK)</span></label>
                <input v-model="form.nip" type="text" placeholder="19850115..." class="input input-bordered input-sm w-full font-mono" />
              </div>
              <div class="form-control">
                <label class="label pb-1"><span class="label-text font-semibold text-xs uppercase">NUPTK</span></label>
                <input v-model="form.nuptk" type="text" placeholder="Nomor NUPTK" class="input input-bordered input-sm w-full font-mono" />
              </div>
              <div class="form-control">
                <label class="label pb-1"><span class="label-text font-semibold text-xs uppercase">NIK (KTP)</span></label>
                <input v-model="form.nik" type="text" placeholder="3201..." class="input input-bordered input-sm w-full font-mono" />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div class="form-control">
                <label class="label pb-1"><span class="label-text font-semibold text-xs uppercase">Jenis Kelamin</span></label>
                <select v-model="form.jenis_kelamin" class="select select-bordered select-sm w-full">
                  <option value="L">Laki-laki</option>
                  <option value="P">Perempuan</option>
                </select>
              </div>
              <div class="form-control">
                <label class="label pb-1"><span class="label-text font-semibold text-xs uppercase">Nomor HP / WhatsApp</span></label>
                <input v-model="form.nomor_hp" type="text" placeholder="08123456789" class="input input-bordered input-sm w-full" />
              </div>
            </div>

            <div class="form-control">
              <label class="label pb-1"><span class="label-text font-semibold text-xs uppercase">Email</span></label>
              <input v-model="form.email" type="email" placeholder="budi.santoso@sekolah.sch.id" class="input input-bordered input-sm w-full" />
            </div>
          </div>

          <!-- Bagian Kepegawaian -->
          <div class="space-y-3 pt-2">
            <h4 class="font-bold text-xs uppercase tracking-wider text-primary border-b border-base-200 pb-1">
              2. Status & Penugasan Kerja
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div class="form-control">
                <label class="label pb-1"><span class="label-text font-semibold text-xs uppercase">Status Kepegawaian</span></label>
                <select v-model="form.status_kepegawaian" class="select select-bordered select-sm w-full">
                  <option v-for="s in props.options.statusOptions" :key="s.value" :value="s.value">
                    {{ s.label }}
                  </option>
                </select>
              </div>
              <div class="form-control">
                <label class="label pb-1"><span class="label-text font-semibold text-xs uppercase">Jabatan Utama</span></label>
                <input v-model="form.jabatan" type="text" placeholder="Contoh: Guru Matematika / Staf TU" class="input input-bordered input-sm w-full" />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div class="form-control">
                <label class="label pb-1"><span class="label-text font-semibold text-xs uppercase">Pendidikan Terakhir</span></label>
                <input v-model="form.pendidikan_terakhir" type="text" placeholder="S1 / S2 / D3 / SMA" class="input input-bordered input-sm w-full" />
              </div>
              <div class="form-control">
                <label class="label pb-1"><span class="label-text font-semibold text-xs uppercase">Jurusan Pendidikan</span></label>
                <input v-model="form.jurusan_pendidikan" type="text" placeholder="Contoh: Pendidikan Matematika" class="input input-bordered input-sm w-full" />
              </div>
            </div>
          </div>

          <!-- Bagian Khusus Guru (Jika Kategori Pendidik) -->
          <div v-if="form.kategori_pegawai === 'pendidik'" class="space-y-3 p-4 bg-primary/5 border border-primary/20 rounded-2xl mt-3">
            <h4 class="font-bold text-xs uppercase tracking-wider text-primary">
              3. Data Khusus Tenaga Pendidik (Guru)
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div class="form-control">
                <label class="label pb-1"><span class="label-text font-semibold text-xs uppercase">Mata Pelajaran / Bidang Studi</span></label>
                <input v-model="form.bidang_studi_utama" type="text" placeholder="Contoh: Matematika, Bahasa Inggris" class="input input-bordered input-sm w-full" />
              </div>
              <div class="form-control">
                <label class="label pb-1"><span class="label-text font-semibold text-xs uppercase">Tugas Tambahan</span></label>
                <input v-model="form.tugas_tambahan" type="text" placeholder="Wali Kelas / Kepala Bengkel" class="input input-bordered input-sm w-full" />
              </div>
            </div>

            <div class="form-control pt-1">
              <label class="label cursor-pointer justify-start gap-3">
                <input v-model="form.is_sertifikasi" type="checkbox" class="checkbox checkbox-primary checkbox-sm rounded" />
                <span class="label-text text-xs font-semibold">Telah lulus sertifikasi pendidik (PPG / TPG)</span>
              </label>
            </div>

            <div v-if="form.is_sertifikasi" class="form-control">
              <label class="label pb-1"><span class="label-text font-semibold text-xs uppercase">Nomor Registrasi Guru / No. Sertifikasi</span></label>
              <input v-model="form.no_sertifikasi" type="text" placeholder="Nomor Sertifikat / NRG" class="input input-bordered input-sm w-full font-mono" />
            </div>
          </div>

          <!-- Status Aktif Toggle -->
          <div class="form-control pt-2">
            <label class="label cursor-pointer justify-start gap-3">
              <input v-model="form.is_active" type="checkbox" class="checkbox checkbox-primary checkbox-sm rounded" />
              <span class="label-text text-xs">Status aktif bekerja di sekolah</span>
            </label>
          </div>

          <!-- Action Buttons -->
          <div class="modal-action pt-4 border-t border-base-200">
            <button type="button" @click="closeModal" class="btn btn-ghost btn-sm" :disabled="form.processing">
              Batal
            </button>
            <button type="submit" class="btn btn-primary btn-sm" :disabled="form.processing">
              <span v-if="form.processing" class="loading loading-spinner loading-xs"></span>
              <span>{{ modalMode === 'create' ? 'Simpan Data' : 'Perbarui Data' }}</span>
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

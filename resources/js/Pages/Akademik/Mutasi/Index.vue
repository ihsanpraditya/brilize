<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import AppLayout from '../../../Layouts/AppLayout.vue'

interface MutasiItem {
  id: number
  siswaId: number
  siswaNama: string
  siswaNis: string
  siswaNisn?: string | null
  tahunPelajaranId: number
  tahunPelajaranNama: string
  jenis: string
  jenisLabel: string
  jenisBadge: string
  kelasAsalId?: number | null
  kelasAsalNama?: string | null
  kelasTujuanId?: number | null
  kelasTujuanNama?: string | null
  tanggalMutasi: string
  nomorSurat?: string | null
  sekolahAsalTujuan?: string | null
  alasan?: string | null
  createdAt: string
}

interface OptionTahun {
  id: number
  nama: string
  isActive: boolean
}

interface OptionKelas {
  id: number
  nama: string
  tingkat: number
  jurusan?: string | null
}

interface OptionSiswa {
  id: number
  nis: string
  nisn?: string | null
  nama: string
  kelasId?: number | null
  kelasNama?: string | null
  status: string
  statusLabel: string
}

interface OptionJenis {
  value: string
  label: string
  badge: string
}

interface Props {
  mutasiList: MutasiItem[]
  options: {
    activeTahunId?: number | null
    tahunOptions: OptionTahun[]
    kelasOptions: OptionKelas[]
    siswaOptions: OptionSiswa[]
    jenisOptions: OptionJenis[]
  }
  statistics: {
    total: number
    totalPindahRombel: number
    totalMutasiMasuk: number
    totalMutasiKeluar: number
    totalLulus: number
  }
  filters: {
    q?: string | null
    jenis?: string | null
    tahun_id?: number | null
    kelas_id?: number | null
  }
}

const props = defineProps<Props>()

const search = ref(props.filters.q ?? '')
const selectedJenis = ref(props.filters.jenis ?? '')
const selectedTahun = ref(props.filters.tahun_id ? String(props.filters.tahun_id) : '')
const selectedKelas = ref(props.filters.kelas_id ? String(props.filters.kelas_id) : '')

function applyFilter() {
  router.get(
    '/akademik/mutasi',
    {
      q: search.value || undefined,
      jenis: selectedJenis.value || undefined,
      tahun_id: selectedTahun.value || undefined,
      kelas_id: selectedKelas.value || undefined,
    },
    {
      preserveState: true,
      replace: true,
      preserveScroll: true,
    }
  )
}

watch([selectedJenis, selectedTahun, selectedKelas], () => {
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
  selectedJenis.value = ''
  selectedTahun.value = ''
  selectedKelas.value = ''
}

// Modal Form State
const isModalOpen = ref(false)
const form = useForm({
  siswa_id: '' as string | number,
  tahun_pelajaran_id: (props.options.activeTahunId ?? '') as string | number,
  jenis: 'pindah_kelas',
  kelas_asal_id: '' as string | number,
  kelas_tujuan_id: '' as string | number,
  tanggal_mutasi: new Date().toISOString().split('T')[0],
  nomor_surat: '',
  sekolah_asal_tujuan: '',
  alasan: '',
})

// Auto-fill kelas asal saat siswa dipilih
watch(
  () => form.siswa_id,
  (newSiswaId) => {
    if (!newSiswaId) return
    const found = props.options.siswaOptions.find((s) => s.id === Number(newSiswaId))
    if (found && found.kelasId) {
      form.kelas_asal_id = found.kelasId
    } else {
      form.kelas_asal_id = ''
    }
  }
)

function openCreateModal() {
  form.reset()
  form.clearErrors()
  form.tahun_pelajaran_id = props.options.activeTahunId ?? ''
  form.tanggal_mutasi = new Date().toISOString().split('T')[0]
  form.jenis = 'pindah_kelas'
  isModalOpen.value = true
}

function closeModal() {
  isModalOpen.value = false
  form.reset()
}

function submit() {
  form.post('/akademik/mutasi', {
    preserveScroll: true,
    onSuccess: () => closeModal(),
  })
}

function hapus(item: MutasiItem) {
  if (confirm(`Hapus catatan riwayat mutasi untuk siswa "${item.siswaNama}" (${item.jenisLabel})?`)) {
    router.delete(`/akademik/mutasi/${item.id}`, {
      preserveScroll: true,
    })
  }
}

// Info Dampak Aksi Form
const impactDescription = computed(() => {
  switch (form.jenis) {
    case 'naik_kelas':
      return 'Siswa akan dipindahkan ke Kelas Tujuan dan status siswa dipastikan tetap Aktif.'
    case 'pindah_kelas':
      return 'Siswa akan dialihkan ke Kelas/Rombel Tujuan dan status siswa tetap Aktif.'
    case 'tinggal_kelas':
      return 'Siswa akan dialokasikan ke Kelas yang dipilih untuk mengulang dan status tetap Aktif.'
    case 'masuk':
      return 'Siswa pindahan akan didaftarkan ke Kelas Tujuan dan status siswa diatur menjadi Aktif.'
    case 'keluar':
      return 'Status siswa akan diubah menjadi "Mutasi / Pindah" dan alokasi kelas aktif dikosongkan.'
    case 'lulus':
      return 'Status siswa akan diubah menjadi "Lulus / Alumni".'
    case 'dropout':
      return 'Status siswa akan diubah menjadi "Keluar / Drop Out" dan alokasi kelas aktif dikosongkan.'
    default:
      return ''
  }
})
</script>

<template>
  <AppLayout>
    <Head title="Riwayat & Mutasi Siswa - Portal Akademik" />

    <div class="space-y-6">

      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-base-content">Riwayat & Mutasi Siswa</h1>
          <p class="text-sm opacity-70">
            Pencatatan perpindahan rombel, kenaikan kelas, mutasi masuk/keluar sekolah, dan kelulusan siswa.
          </p>
        </div>
        <div>
          <button @click="openCreateModal" class="btn btn-primary btn-sm md:btn-md shadow-sm gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            + Catat Mutasi Baru
          </button>
        </div>
      </div>

      <!-- Counter Cards Statistik -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <div class="stat bg-base-100 rounded-2xl shadow-sm border border-base-200 p-4">
          <div class="stat-title text-xs font-semibold uppercase">Total Riwayat</div>
          <div class="stat-value text-primary text-2xl mt-1">{{ props.statistics.total }}</div>
          <div class="stat-desc text-xs mt-1">Seluruh mutasi tercatat</div>
        </div>

        <div class="stat bg-base-100 rounded-2xl shadow-sm border border-base-200 p-4">
          <div class="stat-title text-xs font-semibold uppercase">Kenaikan & Pindah Rombel</div>
          <div class="stat-value text-success text-2xl mt-1">{{ props.statistics.totalPindahRombel }}</div>
          <div class="stat-desc text-xs mt-1">Perubahan kelas siswa</div>
        </div>

        <div class="stat bg-base-100 rounded-2xl shadow-sm border border-base-200 p-4">
          <div class="stat-title text-xs font-semibold uppercase">Mutasi Masuk</div>
          <div class="stat-value text-info text-2xl mt-1">{{ props.statistics.totalMutasiMasuk }}</div>
          <div class="stat-desc text-xs mt-1">Pindahan dari sekolah luar</div>
        </div>

        <div class="stat bg-base-100 rounded-2xl shadow-sm border border-base-200 p-4">
          <div class="stat-title text-xs font-semibold uppercase">Mutasi Keluar & DO</div>
          <div class="stat-value text-warning text-2xl mt-1">{{ props.statistics.totalMutasiKeluar }}</div>
          <div class="stat-desc text-xs mt-1">Pindah sekolah / berhenti</div>
        </div>

        <div class="stat bg-base-100 rounded-2xl shadow-sm border border-base-200 p-4">
          <div class="stat-title text-xs font-semibold uppercase">Kelulusan</div>
          <div class="stat-value text-accent text-2xl mt-1">{{ props.statistics.totalLulus }}</div>
          <div class="stat-desc text-xs mt-1">Siswa lulus / alumni</div>
        </div>
      </div>

      <!-- Filter Bar: Search, Dropdowns & Quick Tabs -->
      <div class="space-y-3">
        <!-- Quick Filter Tabs -->
        <div class="tabs tabs-box bg-base-200 p-1 rounded-2xl overflow-x-auto flex-nowrap w-fit max-w-full">
          <button
            @click="selectedJenis = ''"
            class="tab text-xs font-semibold whitespace-nowrap"
            :class="{ 'tab-active bg-base-100 shadow-sm text-primary': selectedJenis === '' }"
          >
            Semua Riwayat ({{ props.statistics.total }})
          </button>
          <button
            @click="selectedJenis = 'naik_kelas'"
            class="tab text-xs font-semibold whitespace-nowrap"
            :class="{ 'tab-active bg-base-100 shadow-sm text-primary': selectedJenis === 'naik_kelas' }"
          >
            Kenaikan Kelas
          </button>
          <button
            @click="selectedJenis = 'pindah_kelas'"
            class="tab text-xs font-semibold whitespace-nowrap"
            :class="{ 'tab-active bg-base-100 shadow-sm text-primary': selectedJenis === 'pindah_kelas' }"
          >
            Pindah Rombel
          </button>
          <button
            @click="selectedJenis = 'masuk'"
            class="tab text-xs font-semibold whitespace-nowrap"
            :class="{ 'tab-active bg-base-100 shadow-sm text-primary': selectedJenis === 'masuk' }"
          >
            Mutasi Masuk
          </button>
          <button
            @click="selectedJenis = 'keluar'"
            class="tab text-xs font-semibold whitespace-nowrap"
            :class="{ 'tab-active bg-base-100 shadow-sm text-primary': selectedJenis === 'keluar' }"
          >
            Mutasi Keluar
          </button>
          <button
            @click="selectedJenis = 'lulus'"
            class="tab text-xs font-semibold whitespace-nowrap"
            :class="{ 'tab-active bg-base-100 shadow-sm text-primary': selectedJenis === 'lulus' }"
          >
            Kelulusan
          </button>
        </div>

        <!-- Filter Controls -->
        <div class="card bg-base-100 shadow border border-base-200 p-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <div class="form-control">
              <input
                v-model="search"
                type="text"
                placeholder="Cari nama siswa / NIS / no surat..."
                class="input input-bordered input-sm w-full text-xs"
              />
            </div>

            <div class="form-control">
              <select v-model="selectedTahun" class="select select-bordered select-sm w-full text-xs">
                <option value="">Semua Tahun Pelajaran</option>
                <option v-for="t in props.options.tahunOptions" :key="t.id" :value="String(t.id)">
                  {{ t.nama }} {{ t.isActive ? '(Aktif)' : '' }}
                </option>
              </select>
            </div>

            <div class="form-control">
              <select v-model="selectedKelas" class="select select-bordered select-sm w-full text-xs">
                <option value="">Semua Kelas</option>
                <option v-for="k in props.options.kelasOptions" :key="k.id" :value="String(k.id)">
                  {{ k.nama }} (Tingkat {{ k.tingkat }})
                </option>
              </select>
            </div>

            <div class="flex items-center">
              <button
                v-if="search || selectedJenis || selectedTahun || selectedKelas"
                @click="resetFilter"
                class="btn btn-ghost btn-sm text-xs text-error"
              >
                ✕ Reset Filter
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabel Daftar Mutasi Siswa -->
      <div class="card bg-base-100 shadow border border-base-200">
        <div class="card-body p-0">
          <div class="overflow-x-auto">
            <table class="table table-zebra w-full text-sm">
              <thead>
                <tr class="text-xs uppercase text-base-content/60 bg-base-200/50">
                  <th>Tanggal & No. Surat</th>
                  <th>Nama Siswa & Identitas</th>
                  <th>Tahun Pelajaran</th>
                  <th>Jenis Mutasi</th>
                  <th>Pergerakan Kelas</th>
                  <th>Keterangan / Sekolah</th>
                  <th class="text-right">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in props.mutasiList" :key="item.id" class="hover">
                  <!-- Tanggal & Surat -->
                  <td>
                    <div class="font-mono font-bold text-xs text-base-content">{{ item.tanggalMutasi }}</div>
                    <div v-if="item.nomorSurat" class="text-[11px] opacity-60 font-mono">
                      No: {{ item.nomorSurat }}
                    </div>
                    <span v-else class="text-[11px] opacity-40 italic">- Tanpa Surat -</span>
                  </td>

                  <!-- Siswa -->
                  <td>
                    <div class="flex items-center gap-2.5">
                      <div class="avatar placeholder">
                        <div class="w-8 h-8 rounded-full bg-primary/10 text-primary font-bold text-xs shadow-xs">
                          {{ item.siswaNama.charAt(0) }}
                        </div>
                      </div>
                      <div>
                        <div class="font-semibold text-xs text-base-content">{{ item.siswaNama }}</div>
                        <div class="text-[11px] opacity-60 font-mono">NIS: {{ item.siswaNis }}</div>
                      </div>
                    </div>
                  </td>

                  <!-- Tahun Pelajaran -->
                  <td>
                    <span class="text-xs font-medium">{{ item.tahunPelajaranNama }}</span>
                  </td>

                  <!-- Jenis Mutasi Badge -->
                  <td>
                    <span class="badge badge-sm font-semibold" :class="item.jenisBadge">
                      {{ item.jenisLabel }}
                    </span>
                  </td>

                  <!-- Pergerakan Kelas -->
                  <td>
                    <div class="flex items-center gap-1.5 text-xs">
                      <span v-if="item.kelasAsalNama" class="badge badge-outline badge-sm">
                        {{ item.kelasAsalNama }}
                      </span>
                      <span v-else class="text-xs opacity-40 italic">-</span>

                      <span class="text-primary font-bold">→</span>

                      <span v-if="item.kelasTujuanNama" class="badge badge-primary badge-sm font-semibold">
                        {{ item.kelasTujuanNama }}
                      </span>
                      <span v-else class="text-xs opacity-40 italic">-</span>
                    </div>
                  </td>

                  <!-- Keterangan / Asal Tujuan -->
                  <td>
                    <div v-if="item.sekolahAsalTujuan" class="text-xs font-medium text-base-content">
                      🏢 {{ item.sekolahAsalTujuan }}
                    </div>
                    <div v-if="item.alasan" class="text-[11px] opacity-60 truncate max-w-xs">
                      {{ item.alasan }}
                    </div>
                    <span v-if="!item.sekolahAsalTujuan && !item.alasan" class="text-xs opacity-40 italic">-</span>
                  </td>

                  <!-- Aksi -->
                  <td class="text-right whitespace-nowrap">
                    <button @click="hapus(item)" class="btn btn-ghost btn-xs text-error font-semibold">
                      Hapus
                    </button>
                  </td>
                </tr>

                <tr v-if="props.mutasiList.length === 0">
                  <td colspan="7" class="text-center py-12 opacity-60">
                    <div class="flex flex-col items-center justify-center space-y-2">
                      <svg class="w-10 h-10 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                      </svg>
                      <p class="text-sm">Belum ada catatan riwayat mutasi siswa yang sesuai filter.</p>
                      <button @click="openCreateModal" class="btn btn-primary btn-xs mt-1">
                        + Catat Mutasi Pertama
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

    <!-- Modal Form Catat Mutasi Baru -->
    <dialog class="modal" :class="{ 'modal-open': isModalOpen }">
      <div class="modal-box max-w-2xl max-h-[90vh] overflow-y-auto">
        <h3 class="font-bold text-lg text-base-content mb-4 flex items-center gap-2">
          <span>📝 Catat Riwayat & Mutasi Siswa</span>
        </h3>

        <form @submit.prevent="submit" class="space-y-4">

          <!-- 1. Pilih Siswa & Tahun Pelajaran -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="form-control w-full">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase">Pilih Siswa *</span>
              </label>
              <select v-model="form.siswa_id" class="select select-bordered select-sm w-full" required>
                <option value="">-- Pilih Siswa --</option>
                <option v-for="s in props.options.siswaOptions" :key="s.id" :value="s.id">
                  {{ s.nama }} ({{ s.nis }}) {{ s.kelasNama ? '- ' + s.kelasNama : '' }}
                </option>
              </select>
              <div v-if="form.errors.siswa_id" class="text-error text-xs mt-1">{{ form.errors.siswa_id }}</div>
            </div>

            <div class="form-control w-full">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase">Tahun Pelajaran *</span>
              </label>
              <select v-model="form.tahun_pelajaran_id" class="select select-bordered select-sm w-full" required>
                <option v-for="t in props.options.tahunOptions" :key="t.id" :value="t.id">
                  {{ t.nama }} {{ t.isActive ? '(Aktif)' : '' }}
                </option>
              </select>
              <div v-if="form.errors.tahun_pelajaran_id" class="text-error text-xs mt-1">{{ form.errors.tahun_pelajaran_id }}</div>
            </div>
          </div>

          <!-- 2. Jenis Mutasi -->
          <div class="form-control w-full">
            <label class="label pb-1">
              <span class="label-text font-semibold text-xs uppercase">Jenis Mutasi / Riwayat *</span>
            </label>
            <select v-model="form.jenis" class="select select-bordered select-sm w-full" required>
              <option v-for="j in props.options.jenisOptions" :key="j.value" :value="j.value">
                {{ j.label }}
              </option>
            </select>
          </div>

          <!-- Impact Visual Banner -->
          <div v-if="impactDescription" class="p-3 bg-info/10 border border-info/20 rounded-2xl text-xs text-info-content flex items-start gap-2">
            <span class="text-base">ℹ️</span>
            <div>
              <div class="font-bold">Dampak Perubahan Status:</div>
              <div class="opacity-80 mt-0.5">{{ impactDescription }}</div>
            </div>
          </div>

          <!-- 3. Alokasi Kelas Asal & Tujuan -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-base-200/50 rounded-2xl border border-base-200">
            <div class="form-control w-full">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase">Kelas Asal</span>
                <span class="label-text-alt opacity-50">Saat ini</span>
              </label>
              <select v-model="form.kelas_asal_id" class="select select-bordered select-sm w-full">
                <option value="">-- Tidak Ada / Luar Sekolah --</option>
                <option v-for="k in props.options.kelasOptions" :key="k.id" :value="k.id">
                  {{ k.nama }} (Tingkat {{ k.tingkat }})
                </option>
              </select>
            </div>

            <div class="form-control w-full">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase">
                  Kelas Tujuan {{ ['naik_kelas', 'pindah_kelas', 'tinggal_kelas', 'masuk'].includes(form.jenis) ? '*' : '(Opsional)' }}
                </span>
                <span class="label-text-alt opacity-50">Tujuan mutasi</span>
              </label>
              <select
                v-model="form.kelas_tujuan_id"
                class="select select-bordered select-sm w-full"
                :required="['naik_kelas', 'pindah_kelas', 'tinggal_kelas'].includes(form.jenis)"
              >
                <option value="">-- Pilih Kelas Tujuan --</option>
                <option v-for="k in props.options.kelasOptions" :key="k.id" :value="k.id">
                  {{ k.nama }} (Tingkat {{ k.tingkat }})
                </option>
              </select>
            </div>
          </div>

          <!-- 4. Detail Tanggal & Nomor Surat -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="form-control w-full">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase">Tanggal Efektif Mutasi *</span>
              </label>
              <input
                v-model="form.tanggal_mutasi"
                type="date"
                class="input input-bordered input-sm w-full"
                required
              />
            </div>

            <div class="form-control w-full">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase">Nomor Surat Keputusan / Keterangan</span>
                <span class="label-text-alt opacity-50">Opsional</span>
              </label>
              <input
                v-model="form.nomor_surat"
                type="text"
                placeholder="Contoh: 421.3/120/DISDIK/2026"
                class="input input-bordered input-sm w-full font-mono"
              />
            </div>
          </div>

          <!-- 5. Sekolah Asal / Tujuan (Kondisional) -->
          <div v-if="['masuk', 'keluar'].includes(form.jenis)" class="form-control w-full">
            <label class="label pb-1">
              <span class="label-text font-semibold text-xs uppercase">
                {{ form.jenis === 'masuk' ? 'Nama Sekolah Asal' : 'Nama Sekolah Tujuan' }}
              </span>
              <span class="label-text-alt opacity-50">Sekolah luar</span>
            </label>
            <input
              v-model="form.sekolah_asal_tujuan"
              type="text"
              :placeholder="form.jenis === 'masuk' ? 'Contoh: SMP Negeri 1 Jakarta' : 'Contoh: SMA Negeri 3 Bandung'"
              class="input input-bordered input-sm w-full"
            />
          </div>

          <!-- 6. Alasan / Catatan Tambahan -->
          <div class="form-control w-full">
            <label class="label pb-1">
              <span class="label-text font-semibold text-xs uppercase">Alasan Mutasi / Catatan</span>
              <span class="label-text-alt opacity-50">Opsional</span>
            </label>
            <textarea
              v-model="form.alasan"
              rows="2"
              placeholder="Contoh: Mengikuti perpindahan tugas orang tua, peminatan jurusan, dsb..."
              class="textarea textarea-bordered textarea-sm w-full text-xs"
            ></textarea>
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
              <span>Simpan Catatan Mutasi</span>
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

<script setup lang="ts">
import { computed, ref } from 'vue'
import * as XLSX from 'xlsx'

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
  kelasNama?: string | null
  status: string
  statusLabel: string
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
  catatan?: string | null
}

interface Props {
  isOpen: boolean
  filteredSiswaList: SiswaItem[]
  totalSiswaCount: number
  currentFilters: {
    q?: string | null
    kelas_id?: number | null
    status?: string | null
    jenis_kelamin?: string | null
  }
  activeKelasNama?: string | null
}

const props = defineProps<Props>()
const emit = defineEmits<{
  (e: 'close'): void
}>()

// State
const scope = ref<'filtered' | 'all'>('filtered')
const reportType = ref<'ringkas' | 'lengkap'>('ringkas')
const fileFormat = ref<'xlsx' | 'csv'>('xlsx')
const isExporting = ref(false)
const errorMessage = ref('')

// Check if filter is currently applied
const isFilterActive = computed(() => {
  return Boolean(
    props.currentFilters.q ||
    props.currentFilters.kelas_id ||
    props.currentFilters.status ||
    props.currentFilters.jenis_kelamin
  )
})

const estimatedCount = computed(() => {
  if (scope.value === 'filtered') {
    return props.filteredSiswaList.length
  }
  return props.totalSiswaCount
})

// Build formatted rows for export
function formatRowData(list: SiswaItem[], type: 'ringkas' | 'lengkap') {
  if (type === 'ringkas') {
    return list.map((s, idx) => ({
      'No': idx + 1,
      'NIS': s.nis,
      'NISN': s.nisn || '',
      'Nama Lengkap': s.namaLengkap,
      'L/P': s.jenisKelaminLabel || s.jenisKelamin,
      'Kelas': s.kelasNama || 'Belum Ditentukan',
      'Status': s.statusLabel || s.status,
      'No. HP Siswa': s.nomorHp || '',
      'Nama Orang Tua': s.namaAyah || s.namaIbu || s.namaWali || '',
      'No. HP Orang Tua': s.noHpAyah || s.noHpIbu || s.noHpWali || '',
    }))
  }

  // Lengkap / Buku Induk
  return list.map((s, idx) => ({
    'No': idx + 1,
    'NIS': s.nis,
    'NISN': s.nisn || '',
    'NIK': s.nik || '',
    'Nama Lengkap': s.namaLengkap,
    'Nama Panggilan': s.namaPanggilan || '',
    'Nama Arab': s.namaArab || '',
    'Jenis Kelamin': s.jenisKelaminLabel || s.jenisKelamin,
    'Tempat Lahir': s.tempatLahir || '',
    'Tanggal Lahir': s.tanggalLahir || '',
    'Agama': s.agama || 'Islam',
    'Kelas': s.kelasNama || 'Belum Ditentukan',
    'Status': s.statusLabel || s.status,
    'Alamat Tinggal': s.alamat || '',
    'RT': s.rt || '',
    'RW': s.rw || '',
    'Kelurahan': s.kelurahan || '',
    'Kecamatan': s.kecamatan || '',
    'Kab/Kota': s.kabupatenKota || '',
    'Provinsi': s.provinsi || '',
    'Kode Pos': s.kodePos || '',
    'No. HP Siswa': s.nomorHp || '',
    'Email Siswa': s.email || '',
    'Nama Ayah': s.namaAyah || '',
    'Pekerjaan Ayah': s.pekerjaanAyah || '',
    'Penghasilan Ayah': s.penghasilanAyah || '',
    'No. HP Ayah': s.noHpAyah || '',
    'Nama Ibu': s.namaIbu || '',
    'Pekerjaan Ibu': s.pekerjaanIbu || '',
    'Penghasilan Ibu': s.penghasilanIbu || '',
    'No. HP Ibu': s.noHpIbu || '',
    'Nama Wali': s.namaWali || '',
    'Hubungan Wali': s.hubunganWali || '',
    'Pekerjaan Wali': s.pekerjaanWali || '',
    'No. HP Wali': s.noHpWali || '',
    'Alamat Orang Tua': s.alamatOrangTua || '',
    'Anak Ke': s.anakKe ?? '',
    'Jumlah Saudara': s.jumlahSaudara ?? '',
    'Tanggal Masuk': s.tanggalMasuk || '',
    'Asal Sekolah': s.asalSekolah || '',
    'Catatan Khusus': s.catatan || '',
  }))
}

// Calculate dynamic column widths based on content
function getColumnWidths(data: Record<string, any>[]) {
  if (data.length === 0) return []
  const keys = Object.keys(data[0])
  return keys.map(key => {
    let maxLen = key.length
    for (let i = 0; i < Math.min(data.length, 50); i++) {
      const val = String(data[i][key] ?? '')
      if (val.length > maxLen) {
        maxLen = val.length
      }
    }
    return { wch: Math.max(maxLen + 3, 10) }
  })
}

// Handle Export Execution
async function handleExport() {
  errorMessage.value = ''
  isExporting.value = true

  try {
    let studentData: SiswaItem[] = []

    // If scope is 'filtered', use current loaded list
    if (scope.value === 'filtered') {
      studentData = props.filteredSiswaList
    } else {
      // Fetch all students from backend
      const res = await fetch('/akademik/siswa/export?scope=all&format=json')
      if (!res.ok) {
        throw new Error('Gagal mengambil data seluruh siswa dari server.')
      }
      studentData = await res.json()
    }

    if (studentData.length === 0) {
      errorMessage.value = 'Tidak ada data siswa yang dapat diekspor.'
      isExporting.value = false
      return
    }

    const formattedRows = formatRowData(studentData, reportType.value)
    const ws = XLSX.utils.json_to_sheet(formattedRows)
    ws['!cols'] = getColumnWidths(formattedRows)

    const wb = XLSX.utils.book_new()
    const sheetName = reportType.value === 'lengkap' ? 'Buku Induk Siswa' : 'Daftar Siswa'
    XLSX.utils.book_append_sheet(wb, ws, sheetName)

    const dateStr = new Date().toISOString().slice(0, 10).replace(/-/g, '')
    const kelasSuffix = props.activeKelasNama ? `_${props.activeKelasNama.replace(/[^a-zA-Z0-9]/g, '_')}` : ''
    const baseFileName = `Data_Siswa_${reportType.value}${kelasSuffix}_${dateStr}`

    if (fileFormat.value === 'xlsx') {
      XLSX.writeFile(wb, `${baseFileName}.xlsx`)
    } else {
      XLSX.writeFile(wb, `${baseFileName}.csv`, { bookType: 'csv' })
    }

    emit('close')
  } catch (err: any) {
    errorMessage.value = err.message || 'Terjadi kesalahan saat mengekspor data.'
  } finally {
    isExporting.value = false
  }
}
</script>

<template>
  <dialog class="modal modal-bottom sm:modal-middle" :class="{ 'modal-open': props.isOpen }">
    <div class="modal-box max-w-2xl max-h-[92vh] overflow-y-auto p-5 sm:p-7">
      
      <!-- Header Modal -->
      <div class="flex items-start justify-between gap-4 border-b border-base-200 pb-4">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-2xl bg-success/10 text-success flex items-center justify-center text-2xl font-bold shadow-sm">
            📤
          </div>
          <div>
            <h3 class="font-extrabold text-xl text-base-content flex items-center gap-2">
              Ekspor Data Siswa ke Excel
              <span class="badge badge-success badge-sm font-semibold text-success-content">1-Klik Unduh</span>
            </h3>
            <p class="text-xs sm:text-sm opacity-70">
              Unduh data siswa untuk arsip, rekap absen, cetak buku induk, atau laporan sekolah.
            </p>
          </div>
        </div>
        <button @click="emit('close')" type="button" class="btn btn-ghost btn-circle btn-sm">✕</button>
      </div>

      <div class="space-y-5 mt-5">
        
        <!-- Pilihan 1: Cakupan Data Siswa -->
        <div class="space-y-2">
          <label class="text-xs font-bold uppercase tracking-wider text-primary flex items-center gap-1.5">
            <span class="w-5 h-5 rounded-full bg-primary text-primary-content inline-flex items-center justify-center text-[11px]">1</span>
            PILIH CAKUPAN DATA SISWA
          </label>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div
              @click="scope = 'filtered'"
              class="border rounded-2xl p-3.5 cursor-pointer transition-all flex items-start gap-3"
              :class="scope === 'filtered' ? 'border-primary bg-primary/5 shadow-xs' : 'border-base-300 hover:bg-base-200/50'"
            >
              <input
                v-model="scope"
                type="radio"
                value="filtered"
                name="export_scope"
                class="radio radio-primary radio-sm mt-0.5"
              />
              <div>
                <div class="font-bold text-xs sm:text-sm text-base-content">
                  Sesuai Filter Saat Ini
                  <span class="badge badge-primary badge-sm font-mono ml-1 font-bold">
                    {{ props.filteredSiswaList.length }} Siswa
                  </span>
                </div>
                <div class="text-[11px] opacity-60 mt-0.5">
                  <span v-if="props.activeKelasNama">Kelas: <b>{{ props.activeKelasNama }}</b></span>
                  <span v-else-if="isFilterActive">Berdasarkan hasil pencarian/filter di tabel</span>
                  <span v-else>Daftar siswa yang sedang tampil di layar</span>
                </div>
              </div>
            </div>

            <div
              @click="scope = 'all'"
              class="border rounded-2xl p-3.5 cursor-pointer transition-all flex items-start gap-3"
              :class="scope === 'all' ? 'border-primary bg-primary/5 shadow-xs' : 'border-base-300 hover:bg-base-200/50'"
            >
              <input
                v-model="scope"
                type="radio"
                value="all"
                name="export_scope"
                class="radio radio-primary radio-sm mt-0.5"
              />
              <div>
                <div class="font-bold text-xs sm:text-sm text-base-content">
                  Semua Siswa Sekolah
                  <span class="badge badge-neutral badge-sm font-mono ml-1 font-bold">
                    {{ props.totalSiswaCount }} Siswa
                  </span>
                </div>
                <div class="text-[11px] opacity-60 mt-0.5">
                  Seluruh buku induk siswa aktif & terdaftar tanpa filter.
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pilihan 2: Tipe Laporan / Kelengkapan Kolom -->
        <div class="space-y-2">
          <label class="text-xs font-bold uppercase tracking-wider text-primary flex items-center gap-1.5">
            <span class="w-5 h-5 rounded-full bg-primary text-primary-content inline-flex items-center justify-center text-[11px]">2</span>
            PILIH KELENGKAPAN KOLOM / LAPORAN
          </label>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div
              @click="reportType = 'ringkas'"
              class="border rounded-2xl p-3.5 cursor-pointer transition-all flex items-start gap-3"
              :class="reportType === 'ringkas' ? 'border-success bg-success/5 shadow-xs' : 'border-base-300 hover:bg-base-200/50'"
            >
              <input
                v-model="reportType"
                type="radio"
                value="ringkas"
                name="export_type"
                class="radio radio-success radio-sm mt-0.5"
              />
              <div>
                <div class="font-bold text-xs sm:text-sm text-base-content flex items-center gap-1.5">
                  <span>📋 Format Ringkas / Absensi</span>
                </div>
                <div class="text-[11px] opacity-60 mt-1">
                  Berisi: <b>No, NIS, NISN, Nama, L/P, Kelas, Status, No. HP, Nama & No. HP Orang Tua</b>.
                </div>
                <div class="text-[10px] text-success font-medium mt-1">
                  ⭐ Disarankan untuk absensi, wali kelas, & kontak cepat.
                </div>
              </div>
            </div>

            <div
              @click="reportType = 'lengkap'"
              class="border rounded-2xl p-3.5 cursor-pointer transition-all flex items-start gap-3"
              :class="reportType === 'lengkap' ? 'border-success bg-success/5 shadow-xs' : 'border-base-300 hover:bg-base-200/50'"
            >
              <input
                v-model="reportType"
                type="radio"
                value="lengkap"
                name="export_type"
                class="radio radio-success radio-sm mt-0.5"
              />
              <div>
                <div class="font-bold text-xs sm:text-sm text-base-content flex items-center gap-1.5">
                  <span>📚 Format Lengkap / Buku Induk</span>
                </div>
                <div class="text-[11px] opacity-60 mt-1">
                  Semua 39 kolom lengkap: <b>NIK, TTL, Agama, Alamat RT/RW/Kec, Ortu & Pekerjaan, Asal Sekolah, Catatan</b>.
                </div>
                <div class="text-[10px] text-primary font-medium mt-1">
                  ⭐ Disarankan untuk laporan dinas, Dapodik & arsip TU.
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pilihan 3: Format Berkas File -->
        <div class="space-y-2">
          <label class="text-xs font-bold uppercase tracking-wider text-primary flex items-center gap-1.5">
            <span class="w-5 h-5 rounded-full bg-primary text-primary-content inline-flex items-center justify-center text-[11px]">3</span>
            PILIH FORMAT BERKAS
          </label>

          <div class="grid grid-cols-2 gap-3">
            <div
              @click="fileFormat = 'xlsx'"
              class="border rounded-2xl p-3 text-center cursor-pointer transition-all"
              :class="fileFormat === 'xlsx' ? 'border-success bg-success/10 font-bold text-success' : 'border-base-300 opacity-70 hover:opacity-100'"
            >
              <div class="text-2xl mb-1">📊</div>
              <div class="text-xs font-bold">Microsoft Excel (.xlsx)</div>
              <div class="text-[10px] opacity-70 font-normal">Kolom rapi otomatis</div>
            </div>

            <div
              @click="fileFormat = 'csv'"
              class="border rounded-2xl p-3 text-center cursor-pointer transition-all"
              :class="fileFormat === 'csv' ? 'border-success bg-success/10 font-bold text-success' : 'border-base-300 opacity-70 hover:opacity-100'"
            >
              <div class="text-2xl mb-1">📄</div>
              <div class="text-xs font-bold">File CSV (.csv)</div>
              <div class="text-[10px] opacity-70 font-normal">Standar teks pemisah</div>
            </div>
          </div>
        </div>

        <!-- Kartu Ringkasan Unduhan -->
        <div class="p-3.5 rounded-2xl bg-base-200/60 border border-base-300 flex items-center justify-between text-xs">
          <div class="flex items-center gap-2">
            <span class="text-lg">ℹ️</span>
            <span>
              Akan mengunduh <b>{{ estimatedCount }} data siswa</b> dalam format <b>{{ fileFormat.toUpperCase() }}</b> ({{ reportType === 'ringkas' ? 'Format Ringkas' : 'Format Lengkap' }}).
            </span>
          </div>
        </div>

        <!-- Error Alert -->
        <div v-if="errorMessage" class="alert alert-error text-xs shadow-xs">
          <span>⚠️ {{ errorMessage }}</span>
        </div>

      </div>

      <!-- Action Footer -->
      <div class="modal-action pt-4 border-t border-base-200 flex items-center justify-end gap-2">
        <button
          @click="emit('close')"
          type="button"
          class="btn btn-ghost btn-sm"
          :disabled="isExporting"
        >
          Batal
        </button>
        <button
          @click="handleExport"
          type="button"
          class="btn btn-success btn-sm text-success-content font-bold shadow-sm gap-2"
          :disabled="isExporting || estimatedCount === 0"
        >
          <span v-if="isExporting" class="loading loading-spinner loading-xs"></span>
          <span v-else>📥</span>
          <span>{{ isExporting ? 'Menyiapkan Berkas...' : `Unduh ${fileFormat.toUpperCase()} Sekarang` }}</span>
        </button>
      </div>

    </div>

    <!-- Backdrop -->
    <form method="dialog" class="modal-backdrop">
      <button @click="emit('close')">close</button>
    </form>
  </dialog>
</template>

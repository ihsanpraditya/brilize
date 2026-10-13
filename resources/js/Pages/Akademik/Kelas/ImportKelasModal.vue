<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import * as XLSX from 'xlsx'

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
  isOpen: boolean
  activeTahunId?: number | null
  tahunOptions: OptionTahun[]
  guruOptions: OptionGuru[]
  existingKelasNames: string[]
}

const props = defineProps<Props>()
const emit = defineEmits<{
  (e: 'close'): void
  (e: 'success'): void
}>()

// State
const activeTab = ref<'file' | 'paste'>('file')
const targetTahunId = ref<string>(props.activeTahunId ? String(props.activeTahunId) : '')
const onDuplicate = ref<'skip' | 'update'>('skip')
const isDragging = ref(false)
const rawPasteText = ref('')
const fileName = ref('')
const isProcessing = ref(false)
const isSubmitting = ref(false)
const errorMessage = ref('')

// Parsed row interface
interface ParsedKelasRow {
  id: string
  namaKelas: string
  tingkat: number
  jurusan: string
  kapasitas: number
  waliKelas: string
  keterangan: string
  isExisting: boolean
  isValid: boolean
  validationMessage?: string
}

const parsedRows = ref<ParsedKelasRow[]>([])

// Watch target academic year changes
watch(() => props.activeTahunId, (val) => {
  if (val && !targetTahunId.value) {
    targetTahunId.value = String(val)
  }
})

// Auto deduce Tingkat from class name string
function deduceTingkat(name: string): number {
  const upper = name.toUpperCase()
  // Roman numerals check
  if (/\bXII\b|XII[-_ ]/i.test(upper)) return 12
  if (/\bXI\b|XI[-_ ]/i.test(upper)) return 11
  if (/\bX\b|X[-_ ]/i.test(upper)) return 10
  if (/\bIX\b|IX[-_ ]/i.test(upper)) return 9
  if (/\bVIII\b|VIII[-_ ]/i.test(upper)) return 8
  if (/\bVII\b|VII[-_ ]/i.test(upper)) return 7
  if (/\bVI\b|VI[-_ ]/i.test(upper)) return 6
  if (/\bV\b|V[-_ ]/i.test(upper)) return 5
  if (/\bIV\b|IV[-_ ]/i.test(upper)) return 4

  // Numeric check (10, 11, 12, 7, 8, 9, 1-6)
  const numMatch = upper.match(/\b(1[0-2]|[1-9])\b/)
  if (numMatch) {
    return parseInt(numMatch[1], 10)
  }

  // Match prefix like 10-A, 10RPL, 7A
  const prefixMatch = upper.match(/^([1-9]|1[0-2])[A-Z_\-]/)
  if (prefixMatch) {
    return parseInt(prefixMatch[1], 10)
  }

  return 10 // default SMA/SMK
}

// Auto deduce Jurusan from class name string
function deduceJurusan(name: string): string {
  const upper = name.toUpperCase()
  const patterns: Record<string, string> = {
    'RPL': 'Rekayasa Perangkat Lunak',
    'TKJ': 'Teknik Komputer & Jaringan',
    'MM': 'Multimedia',
    'DKV': 'Desain Komunikasi Visual',
    'AKL': 'Akuntansi & Keuangan Lembaga',
    'OTKP': 'Otomatisasi & Tata Kelola Perkantoran',
    'BDP': 'Bisnis Daring & Pemasaran',
    'TBSM': 'Teknik & Bisnis Sepeda Motor',
    'TKRO': 'Teknik Kendaraan Ringan Otomotif',
    'IPA': 'MIPA',
    'MIPA': 'MIPA',
    'IPS': 'IPS',
    'BAHASA': 'Bahasa & Budaya',
  }

  for (const [code, label] of Object.entries(patterns)) {
    const regex = new RegExp(`(^|[-_\\s])${code}([-_\\s\\d]|$)`, 'i')
    if (regex.test(upper)) {
      return label
    }
  }

  return ''
}

// Normalize key name from Excel / CSV header
function findHeaderValue(row: Record<string, any>, candidateKeys: string[]): string {
  for (const candidate of candidateKeys) {
    for (const [key, val] of Object.entries(row)) {
      const cleanKey = key.trim().toLowerCase().replace(/[^a-z0-9]/g, '')
      const cleanCand = candidate.trim().toLowerCase().replace(/[^a-z0-9]/g, '')
      if (cleanKey === cleanCand || cleanKey.includes(cleanCand)) {
        return String(val ?? '').trim()
      }
    }
  }
  return ''
}

// Process raw array of objects
function processRawRows(raw: Record<string, any>[]) {
  errorMessage.value = ''
  if (!raw || raw.length === 0) {
    errorMessage.value = 'Tidak ada data yang terbaca dari file atau teks yang dimasukkan.'
    parsedRows.value = []
    return
  }

  const existingSet = new Set(props.existingKelasNames.map(n => n.trim().toLowerCase()))
  const seenInImport = new Set<string>()
  const result: ParsedKelasRow[] = []

  let rowCounter = 1
  for (const row of raw) {
    // Attempt header lookups
    let namaKelas = findHeaderValue(row, ['namakelas', 'kelas', 'rombel', 'nama'])
    
    // If not found by name, check first column
    if (!namaKelas) {
      const values = Object.values(row).map(v => String(v ?? '').trim()).filter(v => v !== '')
      if (values.length > 0) {
        namaKelas = values[0]
      }
    }

    if (!namaKelas) continue

    const lowerName = namaKelas.toLowerCase()
    const isDuplicateInBatch = seenInImport.has(lowerName)
    seenInImport.add(lowerName)

    const rawTingkat = findHeaderValue(row, ['tingkat', 'jenjang', 'grade', 'level'])
    const tingkat = rawTingkat ? parseInt(rawTingkat, 10) || deduceTingkat(namaKelas) : deduceTingkat(namaKelas)

    let jurusan = findHeaderValue(row, ['jurusan', 'peminatan', 'program'])
    if (!jurusan) {
      jurusan = deduceJurusan(namaKelas)
    }

    const rawKapasitas = findHeaderValue(row, ['kapasitas', 'kuota', 'dayatampung'])
    const kapasitas = rawKapasitas ? parseInt(rawKapasitas, 10) || 36 : 36

    const waliKelas = findHeaderValue(row, ['walikelas', 'wali', 'guru'])
    const keterangan = findHeaderValue(row, ['keterangan', 'ruang', 'ruangan', 'catatan'])

    const isAlreadyInDb = existingSet.has(lowerName)

    let isValid = true
    let validationMessage = ''

    if (namaKelas.length < 2) {
      isValid = false
      validationMessage = 'Nama kelas terlalu pendek'
    } else if (isDuplicateInBatch) {
      isValid = false
      validationMessage = 'Duplikat dalam daftar import ini'
    }

    result.push({
      id: `row-${rowCounter++}`,
      namaKelas,
      tingkat,
      jurusan,
      kapasitas,
      waliKelas,
      keterangan,
      isExisting: isAlreadyInDb,
      isValid,
      validationMessage: validationMessage || (isAlreadyInDb ? 'Sudah ada di database' : 'Siap disimpan'),
    })
  }

  if (result.length === 0) {
    errorMessage.value = 'Tidak ada baris data kelas yang valid ditemukan.'
  }

  parsedRows.value = result
}

// File drop / select handler
function handleFileSelect(e: Event) {
  const target = e.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    readFile(target.files[0])
  }
}

function handleDrop(e: DragEvent) {
  isDragging.value = false
  if (e.dataTransfer && e.dataTransfer.files.length > 0) {
    readFile(e.dataTransfer.files[0])
  }
}

function readFile(file: File) {
  fileName.value = file.name
  isProcessing.value = true
  errorMessage.value = ''

  const reader = new FileReader()
  reader.onload = (e) => {
    try {
      const data = new Uint8Array(e.target?.result as ArrayBuffer)
      const workbook = XLSX.read(data, { type: 'array' })
      const firstSheetName = workbook.SheetNames[0]
      const worksheet = workbook.Sheets[firstSheetName]
      const jsonRows = XLSX.utils.sheet_to_json<Record<string, any>>(worksheet, { defval: '' })
      processRawRows(jsonRows)
    } catch (err: any) {
      errorMessage.value = `Gagal membaca file: ${err.message || 'Format tidak didukung'}`
    } finally {
      isProcessing.value = false
    }
  }

  reader.onerror = () => {
    errorMessage.value = 'Gagal membaca berkas. Silakan coba lagi.'
    isProcessing.value = false
  }

  reader.readAsArrayBuffer(file)
}

// Copy-paste text handler
function parsePastedText() {
  if (!rawPasteText.value.trim()) {
    errorMessage.value = 'Silakan tempel (paste) data dari Excel atau teks terlebih dahulu.'
    return
  }

  const lines = rawPasteText.value.trim().split(/\r?\n/)
  if (lines.length === 0) return

  // Detect delimiter
  const firstLine = lines[0]
  let delimiter = '\t'
  if (firstLine.includes('\t')) {
    delimiter = '\t'
  } else if (firstLine.includes(';') && !firstLine.includes(',')) {
    delimiter = ';'
  } else if (firstLine.includes(',')) {
    delimiter = ','
  }

  const rawHeaders = firstLine.split(delimiter).map(s => s.trim().replace(/^["']|["']$/g, ''))
  const isHeader = rawHeaders.some(h => /kelas|tingkat|nama|jurusan|rombel/i.test(h))

  const startIndex = isHeader ? 1 : 0
  const headers = isHeader ? rawHeaders : []

  const rows: Record<string, any>[] = []
  for (let i = startIndex; i < lines.length; i++) {
    const line = lines[i].trim()
    if (!line) continue
    const cols = line.split(delimiter).map(s => s.trim().replace(/^["']|["']$/g, ''))

    if (isHeader) {
      const obj: Record<string, any> = {}
      headers.forEach((h, idx) => {
        obj[h] = cols[idx] ?? ''
      })
      rows.push(obj)
    } else {
      rows.push({
        'Nama Kelas': cols[0] ?? '',
        'Tingkat': cols[1] ?? '',
        'Jurusan': cols[2] ?? '',
        'Kapasitas': cols[3] ?? '',
        'Wali Kelas': cols[4] ?? '',
        'Keterangan': cols[5] ?? '',
      })
    }
  }

  processRawRows(rows)
}

// Download template with realistic Indonesian high school & junior high examples
function downloadTemplate(format: 'xlsx' | 'csv') {
  const sampleData = [
    {
      'Nama Kelas': 'X-RPL-1',
      'Tingkat': 10,
      'Jurusan': 'Rekayasa Perangkat Lunak',
      'Kapasitas': 36,
      'Wali Kelas': '',
      'Keterangan': 'Lantai 1 Lab Komputer',
    },
    {
      'Nama Kelas': 'X-TKJ-1',
      'Tingkat': 10,
      'Jurusan': 'Teknik Komputer & Jaringan',
      'Kapasitas': 36,
      'Wali Kelas': '',
      'Keterangan': 'Lantai 1 Gedung B',
    },
    {
      'Nama Kelas': 'XI-MIPA-1',
      'Tingkat': 11,
      'Jurusan': 'MIPA',
      'Kapasitas': 34,
      'Wali Kelas': '',
      'Keterangan': 'Lantai 2 Gedung Utama',
    },
    {
      'Nama Kelas': 'XII-IPS-2',
      'Tingkat': 12,
      'Jurusan': 'IPS',
      'Kapasitas': 36,
      'Wali Kelas': '',
      'Keterangan': 'Lantai 2 Gedung Barat',
    },
    {
      'Nama Kelas': 'VII-A',
      'Tingkat': 7,
      'Jurusan': '',
      'Kapasitas': 32,
      'Wali Kelas': '',
      'Keterangan': 'Khusus SMP/MTs jika relevan',
    },
  ]

  const ws = XLSX.utils.json_to_sheet(sampleData)

  // Set column widths for readability
  ws['!cols'] = [
    { wch: 15 }, // Nama Kelas
    { wch: 10 }, // Tingkat
    { wch: 28 }, // Jurusan
    { wch: 12 }, // Kapasitas
    { wch: 22 }, // Wali Kelas
    { wch: 30 }, // Keterangan
  ]

  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'Template Kelas')

  if (format === 'xlsx') {
    XLSX.writeFile(wb, 'Format_Contoh_Import_Kelas.xlsx')
  } else {
    XLSX.writeFile(wb, 'Format_Contoh_Import_Kelas.csv', { bookType: 'csv' })
  }
}

// Reset everything to start fresh
function resetData() {
  parsedRows.value = []
  rawPasteText.value = ''
  fileName.value = ''
  errorMessage.value = ''
}

// Metrics
const countTotal = computed(() => parsedRows.value.length)
const countValidNew = computed(() => parsedRows.value.filter(r => r.isValid && !r.isExisting).length)
const countExisting = computed(() => parsedRows.value.filter(r => r.isExisting).length)
const countInvalid = computed(() => parsedRows.value.filter(r => !r.isValid).length)

// Submit import to server
function submitImport() {
  if (parsedRows.value.length === 0) return

  const validItems = parsedRows.value
    .filter(r => r.isValid)
    .filter(r => onDuplicate.value === 'update' || !r.isExisting)

  if (validItems.length === 0) {
    alert('Tidak ada kelas baru yang dapat disimpan (semua kelas sudah ada atau tidak valid). Jika ingin mengubah data kelas yang sudah ada, pilih opsi "Perbarui data yang sudah ada".')
    return
  }

  isSubmitting.value = true

  router.post('/akademik/kelas/import', {
    tahun_pelajaran_id: targetTahunId.value ? parseInt(targetTahunId.value, 10) : null,
    on_duplicate: onDuplicate.value,
    items: validItems.map(item => ({
      nama_kelas: item.namaKelas,
      tingkat: item.tingkat,
      jurusan: item.jurusan || null,
      kapasitas: item.kapasitas,
      wali_kelas: item.waliKelas || null,
      keterangan: item.keterangan || null,
      is_active: true,
    })),
  }, {
    preserveScroll: true,
    onSuccess: () => {
      isSubmitting.value = false
      resetData()
      emit('success')
      emit('close')
    },
    onError: (errors) => {
      isSubmitting.value = false
      errorMessage.value = Object.values(errors).join(' ') || 'Gagal mengimpor data kelas. Silakan periksa kembali.'
    },
  })
}
</script>

<template>
  <dialog class="modal modal-bottom sm:modal-middle" :class="{ 'modal-open': props.isOpen }">
    <div class="modal-box max-w-4xl max-h-[92vh] overflow-y-auto p-5 sm:p-7">
      
      <!-- Header Modal -->
      <div class="flex items-start justify-between gap-4 border-b border-base-200 pb-4">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center text-2xl font-bold shadow-sm">
            📥
          </div>
          <div>
            <h3 class="font-extrabold text-xl text-base-content flex items-center gap-2">
              Import Kelas Sekaligus
              <span class="badge badge-primary badge-sm font-semibold">Cepat & Praktis</span>
            </h3>
            <p class="text-xs sm:text-sm opacity-70">
              Tidak perlu ketik satu-satu. Cukup upload file Excel/CSV atau copy-paste dari Excel.
            </p>
          </div>
        </div>
        <button @click="emit('close')" type="button" class="btn btn-ghost btn-circle btn-sm">✕</button>
      </div>

      <!-- Step 1: Download Format / Template (Sangat Ramah Boomer) -->
      <div class="mt-4 p-4 rounded-2xl bg-base-200/60 border border-base-300">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <div class="text-xs font-bold uppercase tracking-wider text-primary flex items-center gap-1.5">
              <span class="w-5 h-5 rounded-full bg-primary text-primary-content inline-flex items-center justify-center text-[11px]">1</span>
              LANGKAH 1: BELUM PUNYA FORMAT?
            </div>
            <p class="text-xs opacity-75 mt-0.5">
              Unduh contoh file ini, buka di Microsoft Excel, lalu isi data kelas sekolah Anda.
            </p>
          </div>
          <div class="flex items-center gap-2 flex-wrap">
            <button
              @click="downloadTemplate('xlsx')"
              type="button"
              class="btn btn-sm btn-success text-success-content font-bold shadow-xs gap-1.5"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
              Unduh Contoh Excel (.xlsx)
            </button>
            <button
              @click="downloadTemplate('csv')"
              type="button"
              class="btn btn-sm btn-outline btn-neutral text-xs font-semibold gap-1"
            >
              Unduh CSV
            </button>
          </div>
        </div>
        <div class="mt-2.5 text-[11px] opacity-70 bg-base-100 p-2.5 rounded-xl border border-base-200 flex items-start gap-2">
          <span class="text-info text-base leading-none">💡</span>
          <span>
            <b>Tips Praktis:</b> Anda bahkan cukup mengisi kolom <b>Nama Kelas</b> saja (contoh: <code>X-RPL-1</code>, <code>XI-IPA-2</code>, atau <code>VII-A</code>). Sistem cerdas kami akan <b>otomatis menebak tingkat dan jurusannya</b>!
          </span>
        </div>
      </div>

      <!-- Step 2: Pilih Metode Input Data -->
      <div class="mt-5 space-y-3">
        <div class="text-xs font-bold uppercase tracking-wider text-primary flex items-center gap-1.5">
          <span class="w-5 h-5 rounded-full bg-primary text-primary-content inline-flex items-center justify-center text-[11px]">2</span>
          LANGKAH 2: PILIH CARA MEMASUKKAN DATA
        </div>

        <!-- Tab Selector: File vs Salin-Tempel -->
        <div class="tabs tabs-boxed bg-base-200 p-1 rounded-xl grid grid-cols-2">
          <button
            type="button"
            class="tab text-xs font-bold py-2 transition-all"
            :class="{ 'tab-active bg-primary text-primary-content shadow': activeTab === 'file' }"
            @click="activeTab = 'file'"
          >
            📁 Cara A: Upload File Excel / CSV
          </button>
          <button
            type="button"
            class="tab text-xs font-bold py-2 transition-all"
            :class="{ 'tab-active bg-primary text-primary-content shadow': activeTab === 'paste' }"
            @click="activeTab = 'paste'"
          >
            📋 Cara B: Salin-Tempel (Copy-Paste) dari Excel
          </button>
        </div>

        <!-- Mode A: File Upload Dropzone -->
        <div v-if="activeTab === 'file'" class="mt-2">
          <div
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
            class="border-2 border-dashed rounded-2xl p-6 sm:p-8 text-center transition-all cursor-pointer hover:border-primary hover:bg-primary/5 flex flex-col items-center justify-center gap-3 relative"
            :class="isDragging ? 'border-primary bg-primary/10 scale-[0.99]' : 'border-base-300 bg-base-100'"
          >
            <input
              type="file"
              accept=".xlsx, .xls, .csv"
              @change="handleFileSelect"
              class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
            />
            
            <div class="w-14 h-14 rounded-2xl bg-base-200 flex items-center justify-center text-3xl">
              {{ fileName ? '📄' : '☁️' }}
            </div>

            <div v-if="!fileName">
              <div class="font-bold text-sm sm:text-base text-base-content">
                Klik untuk Memilih File atau Tarik File ke Sini
              </div>
              <div class="text-xs opacity-60 mt-1">
                Mendukung file Microsoft Excel (<b>.xlsx, .xls</b>) atau file <b>.csv</b>
              </div>
            </div>

            <div v-else class="text-center">
              <div class="font-extrabold text-sm text-primary flex items-center justify-center gap-1.5">
                <span>File terpilih:</span>
                <span class="underline">{{ fileName }}</span>
              </div>
              <div class="text-xs opacity-60 mt-1">
                Klik lagi di area ini jika ingin mengganti file.
              </div>
            </div>
          </div>
        </div>

        <!-- Mode B: Direct Copy-Paste Area -->
        <div v-else class="mt-2 space-y-2">
          <div class="form-control">
            <textarea
              v-model="rawPasteText"
              rows="5"
              placeholder="Buka file Excel Anda, blok baris kelasnya, tekan Ctrl+C, lalu klik di kotak ini dan tekan Ctrl+V..."
              class="textarea textarea-bordered w-full font-mono text-xs leading-relaxed"
            ></textarea>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-[11px] opacity-60">
              Mendukung hasil copy dari Microsoft Excel, Google Sheets, atau format teks berpemisah koma/titik-koma/tab.
            </span>
            <button
              @click="parsePastedText"
              type="button"
              class="btn btn-sm btn-primary font-bold shadow-xs"
              :disabled="!rawPasteText.trim()"
            >
              🔍 Baca & Analisis Teks
            </button>
          </div>
        </div>
      </div>

      <!-- Error Alert Message -->
      <div v-if="errorMessage" class="mt-4 alert alert-error text-xs shadow-xs flex items-center gap-2">
        <span>⚠️</span>
        <span>{{ errorMessage }}</span>
      </div>

      <!-- Step 3: Pengaturan Target Import -->
      <div v-if="parsedRows.length > 0" class="mt-6 p-4 rounded-2xl bg-base-200/50 border border-base-200 space-y-3">
        <div class="text-xs font-bold uppercase tracking-wider text-primary flex items-center gap-1.5">
          <span class="w-5 h-5 rounded-full bg-primary text-primary-content inline-flex items-center justify-center text-[11px]">3</span>
          LANGKAH 3: PENGATURAN TAHUN AJARAN & DUPLIKASI
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- Dropdown Tahun Ajaran Target -->
          <div class="form-control">
            <label class="label py-1">
              <span class="label-text text-xs font-semibold">Tahun Pelajaran Target *</span>
            </label>
            <select v-model="targetTahunId" class="select select-bordered select-sm w-full font-medium">
              <option value="">-- Gunakan Tahun Pelajaran Aktif --</option>
              <option
                v-for="tp in props.tahunOptions"
                :key="tp.id"
                :value="String(tp.id)"
              >
                {{ tp.nama }} {{ tp.isActive ? '(Sedang Aktif)' : '' }}
              </option>
            </select>
          </div>

          <!-- Pilihan Jika Duplikat -->
          <div class="form-control">
            <label class="label py-1">
              <span class="label-text text-xs font-semibold">Jika Nama Kelas Sudah Ada:</span>
            </label>
            <div class="flex items-center gap-3 pt-1">
              <label class="flex items-center gap-1.5 cursor-pointer text-xs">
                <input
                  v-model="onDuplicate"
                  type="radio"
                  value="skip"
                  name="on_duplicate"
                  class="radio radio-primary radio-xs"
                />
                <span class="font-medium">Lewati Saja (Aman)</span>
              </label>
              <label class="flex items-center gap-1.5 cursor-pointer text-xs">
                <input
                  v-model="onDuplicate"
                  type="radio"
                  value="update"
                  name="on_duplicate"
                  class="radio radio-warning radio-xs"
                />
                <span class="font-medium">Perbarui Data</span>
              </label>
            </div>
          </div>
        </div>
      </div>

      <!-- Step 4: Pratinjau (Preview) Data Sebelum Simpan -->
      <div v-if="parsedRows.length > 0" class="mt-6 space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
          <div class="text-xs font-bold uppercase tracking-wider text-primary flex items-center gap-1.5">
            <span class="w-5 h-5 rounded-full bg-primary text-primary-content inline-flex items-center justify-center text-[11px]">4</span>
            PRATINJAU HASIL ANALISIS ({{ countTotal }} KELAS)
          </div>
          <button @click="resetData" type="button" class="btn btn-ghost btn-xs text-xs opacity-70 hover:opacity-100">
            🔄 Ganti / Kosongkan Data
          </button>
        </div>

        <!-- Summary Badges -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
          <div class="p-2.5 rounded-xl bg-base-100 border border-base-200 text-center">
            <div class="text-[11px] opacity-60 uppercase font-semibold">Total Terdeteksi</div>
            <div class="text-lg font-bold text-base-content">{{ countTotal }}</div>
          </div>
          <div class="p-2.5 rounded-xl bg-success/10 border border-success/30 text-center">
            <div class="text-[11px] text-success font-semibold uppercase">Kelas Baru</div>
            <div class="text-lg font-bold text-success">{{ countValidNew }}</div>
          </div>
          <div class="p-2.5 rounded-xl bg-warning/10 border border-warning/30 text-center">
            <div class="text-[11px] text-warning font-semibold uppercase">Sudah Ada di DB</div>
            <div class="text-lg font-bold text-warning">{{ countExisting }}</div>
          </div>
          <div class="p-2.5 rounded-xl bg-error/10 border border-error/30 text-center">
            <div class="text-[11px] text-error font-semibold uppercase">Tidak Valid</div>
            <div class="text-lg font-bold text-error">{{ countInvalid }}</div>
          </div>
        </div>

        <!-- Tabel Preview -->
        <div class="border border-base-200 rounded-2xl overflow-hidden max-h-56 overflow-y-auto">
          <table class="table table-xs table-zebra w-full">
            <thead class="sticky top-0 bg-base-200 z-10 text-[11px]">
              <tr>
                <th class="w-8">No</th>
                <th>Nama Kelas</th>
                <th>Tingkat</th>
                <th>Jurusan</th>
                <th>Kapasitas</th>
                <th>Keterangan</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, idx) in parsedRows" :key="row.id">
                <td class="font-mono text-center">{{ idx + 1 }}</td>
                <td class="font-bold text-primary">{{ row.namaKelas }}</td>
                <td>
                  <span class="badge badge-neutral badge-xs font-bold">Tk. {{ row.tingkat }}</span>
                </td>
                <td>{{ row.jurusan || '-' }}</td>
                <td class="font-mono">{{ row.kapasitas }}</td>
                <td class="opacity-70 text-[11px]">{{ row.keterangan || '-' }}</td>
                <td>
                  <span
                    v-if="!row.isValid"
                    class="badge badge-error badge-xs font-semibold gap-1"
                  >
                    ❌ {{ row.validationMessage }}
                  </span>
                  <span
                    v-else-if="row.isExisting"
                    class="badge badge-warning badge-xs font-semibold gap-1"
                  >
                    ⚠️ {{ onDuplicate === 'skip' ? 'Dilewati' : 'Akan Diupdate' }}
                  </span>
                  <span
                    v-else
                    class="badge badge-success badge-xs font-semibold gap-1"
                  >
                    ✅ Siap Masuk
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Action Footer -->
      <div class="modal-action pt-4 border-t border-base-200 flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="text-xs opacity-60 text-center sm:text-left">
          <span v-if="parsedRows.length > 0">
            Pastikan data sudah sesuai sebelum menekan tombol simpan.
          </span>
          <span v-else>
            Unggah file atau tempelkan teks untuk mulai memeriksa data.
          </span>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
          <button
            @click="emit('close')"
            type="button"
            class="btn btn-ghost btn-sm"
            :disabled="isSubmitting"
          >
            Batal
          </button>
          <button
            @click="submitImport"
            type="button"
            class="btn btn-primary btn-sm font-bold shadow-sm gap-2"
            :disabled="parsedRows.length === 0 || isSubmitting"
          >
            <span v-if="isSubmitting" class="loading loading-spinner loading-xs"></span>
            <span v-else>🚀</span>
            <span>
              {{ isSubmitting ? 'Menyimpan...' : `Simpan ${countValidNew + (onDuplicate === 'update' ? countExisting : 0)} Kelas Sekarang` }}
            </span>
          </button>
        </div>
      </div>

    </div>

    <!-- Backdrop -->
    <form method="dialog" class="modal-backdrop">
      <button @click="emit('close')">close</button>
    </form>
  </dialog>
</template>

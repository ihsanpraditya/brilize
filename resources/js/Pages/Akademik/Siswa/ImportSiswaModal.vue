<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import * as XLSX from 'xlsx'

interface KelasOption {
  id: number
  namaKelas: string
  tingkat: number
  jurusan?: string | null
}

interface Props {
  isOpen: boolean
  kelasList: KelasOption[]
  existingNisList: string[]
}

const props = defineProps<Props>()
const emit = defineEmits<{
  (e: 'close'): void
  (e: 'success'): void
}>()

// State
const activeTab = ref<'file' | 'paste'>('file')
const defaultKelasId = ref<string>('')
const onDuplicate = ref<'skip' | 'update'>('skip')
const createUserAccount = ref(true)
const isDragging = ref(false)
const rawPasteText = ref('')
const fileName = ref('')
const isProcessing = ref(false)
const isSubmitting = ref(false)
const errorMessage = ref('')

// Parsed row interface
interface ParsedSiswaRow {
  id: string
  nis: string
  nisn: string
  nik: string
  namaLengkap: string
  jenisKelamin: 'L' | 'P'
  kelas: string
  tempatLahir: string
  tanggalLahir: string
  alamat: string
  namaAyah: string
  namaIbu: string
  nomorHp: string
  isExisting: boolean
  isValid: boolean
  validationMessage?: string
}

const parsedRows = ref<ParsedSiswaRow[]>([])

// Format or parse Date safely to YYYY-MM-DD
function normalizeDate(raw: any): string {
  if (!raw) return ''

  // If number from Excel serial date (e.g. 39675)
  if (typeof raw === 'number') {
    try {
      const dateObj = XLSX.SSF.parse_date_code(raw)
      if (dateObj) {
        const y = String(dateObj.y).padStart(4, '0')
        const m = String(dateObj.m).padStart(2, '0')
        const d = String(dateObj.d).padStart(2, '0')
        return `${y}-${m}-${d}`
      }
    } catch {
      // ignore
    }
  }

  const str = String(raw).trim()
  if (!str) return ''

  // Check YYYY-MM-DD
  if (/^\d{4}-\d{2}-\d{2}$/.test(str)) {
    return str
  }

  // Check DD/MM/YYYY
  const slashMatch = str.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/)
  if (slashMatch) {
    const d = slashMatch[1].padStart(2, '0')
    const m = slashMatch[2].padStart(2, '0')
    const y = slashMatch[3]
    return `${y}-${m}-${d}`
  }

  // Check DD-MM-YYYY
  const dashMatch = str.match(/^(\d{1,2})-(\d{1,2})-(\d{4})$/)
  if (dashMatch) {
    const d = dashMatch[1].padStart(2, '0')
    const m = dashMatch[2].padStart(2, '0')
    const y = dashMatch[3]
    return `${y}-${m}-${d}`
  }

  return str
}

// Normalize Gender string to 'L' or 'P'
function normalizeGender(raw: any): 'L' | 'P' {
  const str = String(raw ?? '').trim().toUpperCase()
  if (str.startsWith('P') || str.startsWith('W') || str === 'FEMALE') {
    return 'P'
  }
  return 'L'
}

// Find header value from dynamic keys
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
    errorMessage.value = 'Tidak ada data siswa yang terbaca dari file atau teks yang dimasukkan.'
    parsedRows.value = []
    return
  }

  const existingSet = new Set(props.existingNisList.map(n => String(n).trim().toLowerCase()))
  const seenInBatch = new Set<string>()
  const result: ParsedSiswaRow[] = []

  let rowCounter = 1
  for (const row of raw) {
    const nis = findHeaderValue(row, ['nis', 'nomorinduk', 'noinduk'])
    const namaLengkap = findHeaderValue(row, ['namalengkap', 'namasiswa', 'nama'])

    // Skip empty lines
    if (!nis && !namaLengkap) continue

    const lowerNis = nis.toLowerCase()
    const isDuplicateInBatch = seenInBatch.has(lowerNis)
    if (nis) seenInBatch.add(lowerNis)

    const rawJk = findHeaderValue(row, ['jeniskelamin', 'jk', 'gender', 'sex', 'lp'])
    const jenisKelamin = normalizeGender(rawJk)

    const nisn = findHeaderValue(row, ['nisn'])
    const nik = findHeaderValue(row, ['nik', 'ktp', 'nokk'])
    const kelas = findHeaderValue(row, ['kelas', 'rombel', 'tingkat'])
    const tempatLahir = findHeaderValue(row, ['tempatlahir', 'tempat', 'kotasaya'])

    // Raw date could be object/number in Excel
    let rawDate: any = ''
    for (const [k, v] of Object.entries(row)) {
      const cleanKey = k.trim().toLowerCase().replace(/[^a-z0-9]/g, '')
      if (cleanKey.includes('tanggallahir') || cleanKey.includes('tgllahir') || cleanKey === 'tgl') {
        rawDate = v
        break
      }
    }
    const tanggalLahir = normalizeDate(rawDate)

    const alamat = findHeaderValue(row, ['alamat', 'domisili', 'tempattinggal'])
    const namaAyah = findHeaderValue(row, ['namaayah', 'ayah', 'bapak'])
    const namaIbu = findHeaderValue(row, ['namaibu', 'ibu', 'bunda'])
    const nomorHp = findHeaderValue(row, ['nomorhp', 'nohp', 'telepon', 'hp', 'wa', 'whatsapp'])

    const isAlreadyInDb = existingSet.has(lowerNis)

    let isValid = true
    let validationMessage = ''

    if (!nis) {
      isValid = false
      validationMessage = 'NIS belum diisi'
    } else if (!namaLengkap) {
      isValid = false
      validationMessage = 'Nama lengkap belum diisi'
    } else if (isDuplicateInBatch) {
      isValid = false
      validationMessage = 'Duplikat dalam daftar import ini'
    }

    result.push({
      id: `siswa-${rowCounter++}`,
      nis,
      nisn,
      nik,
      namaLengkap,
      jenisKelamin,
      kelas,
      tempatLahir,
      tanggalLahir,
      alamat,
      namaAyah,
      namaIbu,
      nomorHp,
      isExisting: isAlreadyInDb,
      isValid,
      validationMessage: validationMessage || (isAlreadyInDb ? 'NIS sudah terdaftar di database' : 'Siap disimpan'),
    })
  }

  if (result.length === 0) {
    errorMessage.value = 'Tidak ada data siswa yang valid ditemukan dari file atau teks.'
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
    errorMessage.value = 'Silakan tempel (paste) data dari Excel atau tabel terlebih dahulu.'
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
  const isHeader = rawHeaders.some(h => /nis|nama|siswa|kelas|jk|kelamin/i.test(h))

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
      // Fallback column positions: NIS, Nama, JK, Kelas, NISN, Tempat Lahir, Tgl Lahir
      rows.push({
        'NIS': cols[0] ?? '',
        'Nama Lengkap': cols[1] ?? '',
        'Jenis Kelamin': cols[2] ?? 'L',
        'Kelas': cols[3] ?? '',
        'NISN': cols[4] ?? '',
        'Tempat Lahir': cols[5] ?? '',
        'Tanggal Lahir': cols[6] ?? '',
        'Alamat': cols[7] ?? '',
        'Nama Ayah': cols[8] ?? '',
        'Nama Ibu': cols[9] ?? '',
        'No HP': cols[10] ?? '',
      })
    }
  }

  processRawRows(rows)
}

// Download template with realistic Indonesian high school & junior high examples
function downloadTemplate(format: 'xlsx' | 'csv') {
  const sampleData = [
    {
      'NIS': '20261001',
      'Nama Lengkap': 'Ahmad Fauzi',
      'Jenis Kelamin': 'L',
      'Kelas': props.kelasList[0]?.namaKelas || 'X-RPL-1',
      'NISN': '0081234567',
      'NIK': '3201012345670001',
      'Tempat Lahir': 'Jakarta',
      'Tanggal Lahir': '2008-05-14',
      'Alamat': 'Jl. Merdeka No. 12 RT 01 RW 02',
      'Nama Ayah': 'Bambang Sudarmono',
      'Nama Ibu': 'Siti Aminah',
      'No HP': '081234567890',
    },
    {
      'NIS': '20261002',
      'Nama Lengkap': 'Nur Annisa Rahmawati',
      'Jenis Kelamin': 'P',
      'Kelas': props.kelasList[0]?.namaKelas || 'X-RPL-1',
      'NISN': '0081234568',
      'NIK': '3201012345670002',
      'Tempat Lahir': 'Bandung',
      'Tanggal Lahir': '2008-09-22',
      'Alamat': 'Jl. Melati No. 4 RT 03 RW 01',
      'Nama Ayah': 'Joko Susilo',
      'Nama Ibu': 'Dewi Lestari',
      'No HP': '081298765432',
    },
    {
      'NIS': '20261003',
      'Nama Lengkap': 'Muhammad Rizky Pratama',
      'Jenis Kelamin': 'L',
      'Kelas': props.kelasList[1]?.namaKelas || 'X-TKJ-1',
      'NISN': '0081234569',
      'NIK': '3201012345670003',
      'Tempat Lahir': 'Surabaya',
      'Tanggal Lahir': '2008-01-10',
      'Alamat': 'Jl. Mawar Indah No. 8',
      'Nama Ayah': 'Hendra Wijaya',
      'Nama Ibu': 'Rina Marlina',
      'No HP': '081311223344',
    },
    {
      'NIS': '20261004',
      'Nama Lengkap': 'Zahra Aulia Fitri',
      'Jenis Kelamin': 'P',
      'Kelas': props.kelasList[2]?.namaKelas || 'XI-IPA-1',
      'NISN': '0081234570',
      'NIK': '3201012345670004',
      'Tempat Lahir': 'Semarang',
      'Tanggal Lahir': '2007-11-03',
      'Alamat': 'Jl. Kenanga No. 15',
      'Nama Ayah': 'Agus Setiawan',
      'Nama Ibu': 'Sri Wahyuni',
      'No HP': '081355667788',
    },
  ]

  const ws = XLSX.utils.json_to_sheet(sampleData)

  ws['!cols'] = [
    { wch: 12 }, // NIS
    { wch: 26 }, // Nama Lengkap
    { wch: 12 }, // Jenis Kelamin
    { wch: 14 }, // Kelas
    { wch: 14 }, // NISN
    { wch: 18 }, // NIK
    { wch: 15 }, // Tempat Lahir
    { wch: 14 }, // Tanggal Lahir
    { wch: 30 }, // Alamat
    { wch: 20 }, // Nama Ayah
    { wch: 20 }, // Nama Ibu
    { wch: 15 }, // No HP
  ]

  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'Data Siswa')

  if (format === 'xlsx') {
    XLSX.writeFile(wb, 'Format_Contoh_Import_Siswa.xlsx')
  } else {
    XLSX.writeFile(wb, 'Format_Contoh_Import_Siswa.csv', { bookType: 'csv' })
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
    alert('Tidak ada data siswa baru yang dapat disimpan. Jika ingin memperbarui data siswa yang sudah terdaftar, pilih opsi "Perbarui Data Siswa".')
    return
  }

  isSubmitting.value = true

  router.post('/akademik/siswa/import', {
    default_kelas_id: defaultKelasId.value ? parseInt(defaultKelasId.value, 10) : null,
    on_duplicate: onDuplicate.value,
    create_user_account: createUserAccount.value,
    items: validItems.map(item => ({
      nis: item.nis,
      nama_lengkap: item.namaLengkap,
      jenis_kelamin: item.jenisKelamin,
      kelas: item.kelas || null,
      nisn: item.nisn || null,
      nik: item.nik || null,
      tempat_lahir: item.tempatLahir || null,
      tanggal_lahir: item.tanggalLahir || null,
      alamat: item.alamat || null,
      nama_ayah: item.namaAyah || null,
      nama_ibu: item.namaIbu || null,
      nomor_hp: item.nomorHp || null,
      status: 'aktif',
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
      errorMessage.value = Object.values(errors).join(' ') || 'Gagal mengimpor data siswa. Silakan periksa kembali.'
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
            👥
          </div>
          <div>
            <h3 class="font-extrabold text-xl text-base-content flex items-center gap-2">
              Import Data Siswa Sekaligus
              <span class="badge badge-primary badge-sm font-semibold">Cepat & Otomatis</span>
            </h3>
            <p class="text-xs sm:text-sm opacity-70">
              Solusi praktis memasukkan ratusan siswa langsung dari file Excel / CSV tanpa input manual satu per satu.
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
              LANGKAH 1: BELUM PUNYA CONTOH FILE?
            </div>
            <p class="text-xs opacity-75 mt-0.5">
              Unduh contoh file ini, buka di Microsoft Excel, lalu copas data siswa sekolah Anda ke dalamnya.
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
            <b>Tips Cepat & Praktis:</b> Kolom yang wajib hanyalah <b>NIS</b> dan <b>Nama Lengkap</b>. Kolom lainnya (NISN, L/P, TTL, Alamat, Nama Ortu) bersifat opsional. Format tanggal lahir fleksibel (bisa <code>14/05/2008</code> atau <code>2008-05-14</code>).
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
                Klik untuk Memilih File atau Tarik File Excel Siswa ke Sini
              </div>
              <div class="text-xs opacity-60 mt-1">
                Mendukung berkas Microsoft Excel (<b>.xlsx, .xls</b>) atau file <b>.csv</b>
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
              placeholder="Buka file Excel daftar siswa Anda, blok tabelnya, tekan Ctrl+C, lalu klik di sini dan tekan Ctrl+V..."
              class="textarea textarea-bordered w-full font-mono text-xs leading-relaxed"
            ></textarea>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-[11px] opacity-60">
              Mendukung hasil copy langsung dari Microsoft Excel atau Google Sheets.
            </span>
            <button
              @click="parsePastedText"
              type="button"
              class="btn btn-sm btn-primary font-bold shadow-xs"
              :disabled="!rawPasteText.trim()"
            >
              🔍 Baca & Analisis Data
            </button>
          </div>
        </div>
      </div>

      <!-- Error Alert Message -->
      <div v-if="errorMessage" class="mt-4 alert alert-error text-xs shadow-xs flex items-center gap-2">
        <span>⚠️</span>
        <span>{{ errorMessage }}</span>
      </div>

      <!-- Step 3: Pengaturan Penempatan Kelas & Akun Portal -->
      <div v-if="parsedRows.length > 0" class="mt-6 p-4 rounded-2xl bg-base-200/50 border border-base-200 space-y-4">
        <div class="text-xs font-bold uppercase tracking-wider text-primary flex items-center gap-1.5">
          <span class="w-5 h-5 rounded-full bg-primary text-primary-content inline-flex items-center justify-center text-[11px]">3</span>
          LANGKAH 3: PENGATURAN KELAS & DUPLIKASI
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- Dropdown Tetapkan Kelas -->
          <div class="form-control">
            <label class="label py-1">
              <span class="label-text text-xs font-semibold">Penempatan Kelas / Rombel:</span>
            </label>
            <select v-model="defaultKelasId" class="select select-bordered select-sm w-full font-medium">
              <option value="">-- Sesuaikan Kolom Kelas di File / Belum Ditentukan --</option>
              <option
                v-for="k in props.kelasList"
                :key="k.id"
                :value="String(k.id)"
              >
                Tetapkan ke {{ k.namaKelas }} (Tingkat {{ k.tingkat }})
              </option>
            </select>
            <span class="text-[11px] opacity-60 mt-1">
              Pilih kelas jika file Excel tidak memiliki kolom kelas, atau ingin memasukkan semua siswa ke satu kelas.
            </span>
          </div>

          <!-- Pilihan Jika NIS Duplikat -->
          <div class="form-control">
            <label class="label py-1">
              <span class="label-text text-xs font-semibold">Jika NIS Sudah Ada di Database:</span>
            </label>
            <div class="flex items-center gap-3 pt-1">
              <label class="flex items-center gap-1.5 cursor-pointer text-xs">
                <input
                  v-model="onDuplicate"
                  type="radio"
                  value="skip"
                  name="on_duplicate_siswa"
                  class="radio radio-primary radio-xs"
                />
                <span class="font-medium">Lewati Saja (Aman)</span>
              </label>
              <label class="flex items-center gap-1.5 cursor-pointer text-xs">
                <input
                  v-model="onDuplicate"
                  type="radio"
                  value="update"
                  name="on_duplicate_siswa"
                  class="radio radio-warning radio-xs"
                />
                <span class="font-medium">Perbarui Data Siswa</span>
              </label>
            </div>
            <span class="text-[11px] opacity-60 mt-1">
              Pilih "Perbarui Data" jika Anda ingin memperbarui biodata atau nomor HP siswa yang sudah ada.
            </span>
          </div>
        </div>

        <!-- Checkbox Buat Akun Portal Otomatis -->
        <div class="p-3 bg-base-100 rounded-xl border border-base-200">
          <label class="flex items-center gap-3 cursor-pointer">
            <input
              v-model="createUserAccount"
              type="checkbox"
              class="checkbox checkbox-primary checkbox-sm rounded"
            />
            <div>
              <div class="text-xs font-bold text-base-content">
                Buat Akun Portal Siswa Otomatis
              </div>
              <div class="text-[11px] opacity-60">
                Sistem akan membuat akun login portal siswa dengan <b>Username & Password default menggunakan NIS</b>.
              </div>
            </div>
          </label>
        </div>
      </div>

      <!-- Step 4: Pratinjau (Preview) Data Sebelum Simpan -->
      <div v-if="parsedRows.length > 0" class="mt-6 space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
          <div class="text-xs font-bold uppercase tracking-wider text-primary flex items-center gap-1.5">
            <span class="w-5 h-5 rounded-full bg-primary text-primary-content inline-flex items-center justify-center text-[11px]">4</span>
            PRATINJAU HASIL ANALISIS ({{ countTotal }} SISWA)
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
            <div class="text-[11px] text-success font-semibold uppercase">Siswa Baru</div>
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
        <div class="border border-base-200 rounded-2xl overflow-hidden max-h-60 overflow-y-auto">
          <table class="table table-xs table-zebra w-full">
            <thead class="sticky top-0 bg-base-200 z-10 text-[11px]">
              <tr>
                <th class="w-8">No</th>
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th>L/P</th>
                <th>Kelas</th>
                <th>TTL</th>
                <th>Nama Ortu</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, idx) in parsedRows" :key="row.id">
                <td class="font-mono text-center">{{ idx + 1 }}</td>
                <td class="font-bold font-mono text-primary">{{ row.nis }}</td>
                <td>
                  <div class="font-semibold text-base-content">{{ row.namaLengkap }}</div>
                  <div v-if="row.nisn" class="text-[10px] opacity-60 font-mono">NISN: {{ row.nisn }}</div>
                </td>
                <td>
                  <span class="badge badge-xs font-semibold" :class="row.jenisKelamin === 'L' ? 'badge-info' : 'badge-secondary'">
                    {{ row.jenisKelamin }}
                  </span>
                </td>
                <td>
                  <span v-if="defaultKelasId" class="badge badge-outline badge-primary badge-xs">
                    {{ props.kelasList.find(k => String(k.id) === defaultKelasId)?.namaKelas }}
                  </span>
                  <span v-else-if="row.kelas" class="badge badge-outline badge-xs">
                    {{ row.kelas }}
                  </span>
                  <span v-else class="text-[10px] opacity-40 italic">-</span>
                </td>
                <td class="text-[11px]">
                  <span v-if="row.tempatLahir || row.tanggalLahir">
                    {{ row.tempatLahir ? row.tempatLahir + ', ' : '' }}{{ row.tanggalLahir }}
                  </span>
                  <span v-else class="opacity-40">-</span>
                </td>
                <td class="text-[11px]">
                  <span v-if="row.namaAyah || row.namaIbu">
                    {{ row.namaAyah || row.namaIbu }}
                  </span>
                  <span v-else class="opacity-40">-</span>
                </td>
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
            Periksa data di atas sebelum menekan tombol simpan.
          </span>
          <span v-else>
            Unggah file atau tempelkan teks untuk mulai memeriksa data siswa.
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
              {{ isSubmitting ? 'Menyimpan...' : `Simpan ${countValidNew + (onDuplicate === 'update' ? countExisting : 0)} Siswa Sekarang` }}
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

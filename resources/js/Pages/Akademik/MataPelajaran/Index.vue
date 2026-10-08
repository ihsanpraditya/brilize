<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import AppLayout from '../../../Layouts/AppLayout.vue'

interface GuruItem {
  id: number
  nama: string
  nip?: string | null
  bidangStudi?: string | null
}

interface MapelItem {
  id: number
  kodeMapel: string
  namaMapel: string
  kelompok: string
  kelompokLabel: string
  kelompokBadgeClass: string
  tingkat?: number | null
  jurusan?: string | null
  kkm: number
  bebanJamPerMinggu: number
  guruPengampu: Array<{ id: number; nama: string; nip?: string | null }>
  guruPengampuIds: number[]
  guruKoordinatorId?: number | null
  guruKoordinatorNama?: string | null
  urutan: number
  keterangan?: string | null
  isActive: boolean
  createdAt: string
}

interface KelompokOption {
  value: string
  label: string
  badgeClass: string
}

interface Props {
  mapelList: MapelItem[]
  options: {
    kelompok: KelompokOption[]
    guru: GuruItem[]
    tingkat: number[]
  }
  filters: {
    q?: string
    kelompok?: string | null
    tingkat?: string | null
    status?: string | null
  }
}

const props = defineProps<Props>()

const search = ref(props.filters.q ?? '')
const selectedKelompok = ref(props.filters.kelompok ?? '')
const selectedTingkat = ref(props.filters.tingkat ?? '')
const selectedStatus = ref(props.filters.status ?? '')

const isModalOpen = ref(false)
const modalMode = ref<'create' | 'edit'>('create')
const editingId = ref<number | null>(null)
const guruSearch = ref('')

const form = useForm({
  kode_mapel: '',
  nama_mapel: '',
  kelompok: 'wajib',
  tingkat: '' as number | string,
  jurusan: '',
  kkm: 75,
  beban_jam_per_minggu: 2,
  guru_ids: [] as number[],
  guru_koordinator_id: '' as number | string,
  urutan: 0,
  keterangan: '',
  is_active: true,
})

// Filter watcher
function applyFilter() {
  router.get(
    '/akademik/mata-pelajaran',
    {
      q: search.value || undefined,
      kelompok: selectedKelompok.value || undefined,
      tingkat: selectedTingkat.value || undefined,
      status: selectedStatus.value || undefined,
    },
    {
      preserveState: true,
      replace: true,
    }
  )
}

watch([selectedKelompok, selectedTingkat, selectedStatus], () => {
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
  guruSearch.value = ''
  form.reset()
  form.clearErrors()
  form.kelompok = 'wajib'
  form.tingkat = ''
  form.kkm = 75
  form.beban_jam_per_minggu = 2
  form.guru_ids = []
  form.guru_koordinator_id = ''
  form.urutan = 0
  form.is_active = true
  isModalOpen.value = true
}

function openEditModal(item: MapelItem) {
  modalMode.value = 'edit'
  editingId.value = item.id
  guruSearch.value = ''
  form.clearErrors()
  form.kode_mapel = item.kodeMapel
  form.nama_mapel = item.namaMapel
  form.kelompok = item.kelompok
  form.tingkat = item.tingkat ?? ''
  form.jurusan = item.jurusan ?? ''
  form.kkm = item.kkm
  form.beban_jam_per_minggu = item.bebanJamPerMinggu
  form.guru_ids = [...item.guruPengampuIds]
  form.guru_koordinator_id = item.guruKoordinatorId ?? ''
  form.urutan = item.urutan
  form.keterangan = item.keterangan ?? ''
  form.is_active = item.isActive
  isModalOpen.value = true
}

function closeModal() {
  isModalOpen.value = false
  form.reset()
}

function toggleGuruPengampu(guruId: number) {
  const index = form.guru_ids.indexOf(guruId)
  if (index === -1) {
    form.guru_ids.push(guruId)
  } else {
    form.guru_ids.splice(index, 1)
  }
}

function submit() {
  if (modalMode.value === 'create') {
    form.post('/akademik/mata-pelajaran', {
      onSuccess: () => closeModal(),
    })
  } else if (editingId.value !== null) {
    form.put(`/akademik/mata-pelajaran/${editingId.value}`, {
      onSuccess: () => closeModal(),
    })
  }
}

function hapus(item: MapelItem) {
  if (confirm(`Apakah Anda yakin ingin menghapus Mata Pelajaran "${item.namaMapel}" (${item.kodeMapel})?`)) {
    router.delete(`/akademik/mata-pelajaran/${item.id}`, {
      preserveScroll: true,
    })
  }
}
</script>

<template>
  <AppLayout>
    <Head title="Data Mata Pelajaran - ERP Sekolah" />

    <div class="space-y-6">
      <!-- Header Section -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-base-content">Data Mata Pelajaran</h1>
          <p class="text-sm opacity-70">
            Kelola kurikulum mata pelajaran, beban JP, KKM, dan alokasi guru pengampu (1 guru dapat mengajar banyak mapel & 1 mapel dapat diampu banyak guru).
          </p>
        </div>
        <div>
          <button @click="openCreateModal" class="btn btn-primary btn-sm md:btn-md shadow-sm">
            + Tambah Mapel Baru
          </button>
        </div>
      </div>

      <!-- Filter & Search Bar -->
      <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
          <!-- Search Input -->
          <div class="form-control md:col-span-2">
            <input
              v-model="search"
              type="text"
              placeholder="Cari kode / nama mapel / jurusan..."
              class="input input-bordered input-sm w-full"
            />
          </div>

          <!-- Filter Kelompok -->
          <div class="form-control">
            <select v-model="selectedKelompok" class="select select-bordered select-sm w-full">
              <option value="">Semua Kelompok</option>
              <option
                v-for="kel in props.options.kelompok"
                :key="kel.value"
                :value="kel.value"
              >
                {{ kel.label }}
              </option>
            </select>
          </div>

          <!-- Filter Tingkat -->
          <div class="form-control">
            <select v-model="selectedTingkat" class="select select-bordered select-sm w-full">
              <option value="">Semua Tingkat</option>
              <option v-for="t in props.options.tingkat" :key="t" :value="String(t)">
                Tingkat {{ t }}
              </option>
            </select>
          </div>

          <!-- Filter Status -->
          <div class="form-control">
            <select v-model="selectedStatus" class="select select-bordered select-sm w-full">
              <option value="">Semua Status</option>
              <option value="1">Aktif</option>
              <option value="0">Nonaktif</option>
            </select>
          </div>
        </div>

        <div v-if="search || selectedKelompok || selectedTingkat || selectedStatus" class="px-4 pb-3 flex justify-end">
          <button
            @click="
              search = '';
              selectedKelompok = '';
              selectedTingkat = '';
              selectedStatus = '';
            "
            class="btn btn-ghost btn-xs text-xs opacity-70 hover:opacity-100"
          >
            ✕ Reset Filter
          </button>
        </div>
      </div>

      <!-- Tabel Daftar Mata Pelajaran -->
      <div class="card bg-base-100 shadow border border-base-200">
        <div class="card-body p-0">
          <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
              <thead>
                <tr class="text-xs uppercase text-base-content/60 bg-base-200/50">
                  <th class="w-16">Kode</th>
                  <th>Mata Pelajaran</th>
                  <th>Kelompok & Tingkat</th>
                  <th class="text-center">Beban / KKM</th>
                  <th>Guru Pengampu</th>
                  <th>Koordinator</th>
                  <th>Status</th>
                  <th class="text-right">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in props.mapelList" :key="item.id" class="hover">
                  <td>
                    <span class="badge badge-neutral font-mono font-bold text-xs">
                      {{ item.kodeMapel }}
                    </span>
                  </td>
                  <td>
                    <div class="font-bold text-base text-primary">{{ item.namaMapel }}</div>
                    <div v-if="item.jurusan" class="text-xs opacity-60">
                      Jurusan: <span class="font-medium text-base-content">{{ item.jurusan }}</span>
                    </div>
                    <div v-if="item.keterangan" class="text-[11px] opacity-50">{{ item.keterangan }}</div>
                  </td>
                  <td>
                    <div class="flex flex-col gap-1 items-start">
                      <span class="badge badge-sm font-semibold" :class="item.kelompokBadgeClass">
                        {{ item.kelompokLabel }}
                      </span>
                      <span v-if="item.tingkat" class="badge badge-outline badge-xs">
                        Tingkat {{ item.tingkat }}
                      </span>
                      <span v-else class="text-[11px] opacity-50 italic">Semua Tingkat</span>
                    </div>
                  </td>
                  <td class="text-center">
                    <div class="text-xs font-semibold">{{ item.bebanJamPerMinggu }} JP / mg</div>
                    <div class="text-[11px] opacity-60">KKM: {{ item.kkm }}</div>
                  </td>
                  <td>
                    <div v-if="item.guruPengampu.length > 0" class="flex flex-wrap gap-1 max-w-xs">
                      <span
                        v-for="guru in item.guruPengampu"
                        :key="guru.id"
                        class="badge badge-outline badge-xs text-[11px] py-1 gap-1"
                      >
                        <span class="w-1.5 h-1.5 rounded-full bg-primary inline-block"></span>
                        {{ guru.nama }}
                      </span>
                    </div>
                    <span v-else class="text-xs opacity-40 italic">Belum ada guru</span>
                  </td>
                  <td>
                    <div v-if="item.guruKoordinatorNama" class="text-xs font-medium flex items-center gap-1.5">
                      <div class="avatar placeholder">
                        <div class="bg-secondary/10 text-secondary rounded-full w-5 h-5 text-[10px] font-bold">
                          {{ item.guruKoordinatorNama.charAt(0) }}
                        </div>
                      </div>
                      <span>{{ item.guruKoordinatorNama }}</span>
                    </div>
                    <span v-else class="text-xs opacity-40 italic">-</span>
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
                    <button @click="openEditModal(item)" class="btn btn-ghost btn-xs text-primary">
                      Edit
                    </button>
                    <button @click="hapus(item)" class="btn btn-ghost btn-xs text-error">
                      Hapus
                    </button>
                  </td>
                </tr>

                <tr v-if="props.mapelList.length === 0">
                  <td colspan="8" class="text-center py-12 opacity-60">
                    Belum ada data mata pelajaran yang sesuai filter. Klik tombol <b>+ Tambah Mapel Baru</b> untuk menambahkan.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Form Tambah / Edit Mata Pelajaran -->
    <dialog class="modal" :class="{ 'modal-open': isModalOpen }">
      <div class="modal-box max-w-2xl max-h-[90vh] overflow-y-auto">
        <h3 class="font-bold text-lg text-base-content mb-4">
          {{ modalMode === 'create' ? 'Tambah Mata Pelajaran Baru' : 'Edit Mata Pelajaran' }}
        </h3>

        <form @submit.prevent="submit" class="space-y-4">
          <!-- Kode & Nama Mapel -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="form-control w-full sm:col-span-1">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase">Kode Mapel *</span>
              </label>
              <input
                v-model="form.kode_mapel"
                type="text"
                placeholder="MTK-10"
                class="input input-bordered w-full uppercase font-mono font-bold"
                :class="{ 'input-error': form.errors.kode_mapel }"
                required
              />
              <div v-if="form.errors.kode_mapel" class="text-error text-xs mt-1">{{ form.errors.kode_mapel }}</div>
            </div>

            <div class="form-control w-full sm:col-span-2">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase">Nama Mata Pelajaran *</span>
              </label>
              <input
                v-model="form.nama_mapel"
                type="text"
                placeholder="Contoh: Matematika Wajib"
                class="input input-bordered w-full"
                :class="{ 'input-error': form.errors.nama_mapel }"
                required
              />
              <div v-if="form.errors.nama_mapel" class="text-error text-xs mt-1">{{ form.errors.nama_mapel }}</div>
            </div>
          </div>

          <!-- Kelompok & Tingkat -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="form-control w-full">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase">Kelompok Kurikulum *</span>
              </label>
              <select v-model="form.kelompok" class="select select-bordered w-full" required>
                <option
                  v-for="kel in props.options.kelompok"
                  :key="kel.value"
                  :value="kel.value"
                >
                  {{ kel.label }}
                </option>
              </select>
            </div>

            <div class="form-control w-full">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase">Tingkat Jenjang</span>
                <span class="label-text-alt opacity-50">Opsional</span>
              </label>
              <select v-model="form.tingkat" class="select select-bordered w-full">
                <option value="">Semua Tingkat</option>
                <option v-for="t in props.options.tingkat" :key="t" :value="t">
                  Tingkat {{ t }}
                </option>
              </select>
            </div>
          </div>

          <!-- Jurusan, KKM, Beban Jam -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="form-control w-full">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase">Jurusan / Peminatan</span>
              </label>
              <input
                v-model="form.jurusan"
                type="text"
                placeholder="Semua / RPL / MIPA"
                class="input input-bordered w-full"
              />
            </div>

            <div class="form-control w-full">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase">KKM / KKTP</span>
              </label>
              <input
                v-model.number="form.kkm"
                type="number"
                min="0"
                max="100"
                class="input input-bordered w-full"
                required
              />
            </div>

            <div class="form-control w-full">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase">Beban Jam (JP / Mg)</span>
              </label>
              <input
                v-model.number="form.beban_jam_per_minggu"
                type="number"
                min="1"
                max="20"
                class="input input-bordered w-full"
                required
              />
            </div>
          </div>

          <!-- Multi Guru Pengampu (Many to Many Selector) -->
          <div class="form-control w-full">
            <label class="label pb-1 flex justify-between items-center">
              <span class="label-text font-semibold text-xs uppercase">Guru Pengampu (Bisa Lebih dari 1 Guru)</span>
              <span class="label-text-alt font-medium text-primary">
                {{ form.guru_ids.length }} guru dipilih
              </span>
            </label>

            <!-- Search box guru -->
            <input
              v-model="guruSearch"
              type="text"
              placeholder="Ketik untuk memfilter nama guru..."
              class="input input-bordered input-xs w-full mb-2"
            />

            <!-- Daftar Guru Checkboxes -->
            <div class="border border-base-300 rounded-xl p-3 max-h-44 overflow-y-auto space-y-1.5 bg-base-200/40">
              <div
                v-for="guru in props.options.guru.filter(g => !guruSearch || g.nama.toLowerCase().includes(guruSearch.toLowerCase()) || (g.bidangStudi && g.bidangStudi.toLowerCase().includes(guruSearch.toLowerCase())))"
                :key="guru.id"
                @click="toggleGuruPengampu(guru.id)"
                class="flex items-center justify-between p-2 rounded-lg cursor-pointer transition-colors text-xs"
                :class="form.guru_ids.includes(guru.id) ? 'bg-primary/10 border border-primary/30 font-semibold text-primary' : 'hover:bg-base-300/60'"
              >
                <div class="flex items-center gap-2">
                  <input
                    type="checkbox"
                    :checked="form.guru_ids.includes(guru.id)"
                    class="checkbox checkbox-primary checkbox-xs rounded"
                    @click.stop="toggleGuruPengampu(guru.id)"
                  />
                  <span>{{ guru.nama }}</span>
                </div>
                <span v-if="guru.bidangStudi" class="text-[11px] opacity-60 font-normal">
                  {{ guru.bidangStudi }}
                </span>
              </div>

              <div v-if="props.options.guru.length === 0" class="text-center py-3 text-xs opacity-50">
                Belum ada master data guru.
              </div>
            </div>
          </div>

          <!-- Koordinator Mapel & Urutan -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="form-control w-full">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase">Guru Koordinator (Opsional)</span>
              </label>
              <select v-model="form.guru_koordinator_id" class="select select-bordered w-full">
                <option value="">-- Belum Ada Koordinator --</option>
                <option
                  v-for="guru in props.options.guru"
                  :key="guru.id"
                  :value="guru.id"
                >
                  {{ guru.nama }}
                </option>
              </select>
            </div>

            <div class="form-control w-full">
              <label class="label pb-1">
                <span class="label-text font-semibold text-xs uppercase">Urutan Rapor</span>
              </label>
              <input
                v-model.number="form.urutan"
                type="number"
                min="0"
                class="input input-bordered w-full"
              />
            </div>
          </div>

          <!-- Keterangan -->
          <div class="form-control w-full">
            <label class="label pb-1">
              <span class="label-text font-semibold text-xs uppercase">Keterangan Tambahan</span>
            </label>
            <input
              v-model="form.keterangan"
              type="text"
              placeholder="Catatan / Kurikulum khusus"
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
              <span class="label-text text-xs">Mata Pelajaran Aktif dalam Kurikulum</span>
            </label>
          </div>

          <!-- Action Buttons -->
          <div class="modal-action pt-4 border-t border-base-200">
            <button type="button" @click="closeModal" class="btn btn-ghost btn-sm" :disabled="form.processing">
              Batal
            </button>
            <button type="submit" class="btn btn-primary btn-sm" :disabled="form.processing">
              <span v-if="form.processing" class="loading loading-spinner loading-xs"></span>
              <span>{{ modalMode === 'create' ? 'Simpan Mapel' : 'Perbarui Mapel' }}</span>
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

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import api from '../../lib/api'
import { formatPeriode } from '../../lib/periode'
import PeriodeSelect from '../../components/PeriodeSelect.vue'
import StatusPengisianBadge from '../../components/StatusPengisianBadge.vue'

interface Row {
  id: number
  nama: string
  alamat: string
  kepala_puskesmas: string | null
  kode_puskesmas: string | null
  kuesioner_sudah_isi: boolean
  observasi_sudah_isi: boolean
}

const loading = ref(true)
const rows = ref<Row[]>([])
const periode = ref({ bulan: new Date().getMonth() + 1, tahun: new Date().getFullYear() })
const search = ref('')

const filtered = computed(() =>
  rows.value.filter((r) => r.nama.toLowerCase().includes(search.value.toLowerCase())),
)

async function load() {
  loading.value = true
  const { data } = await api.get('/api/dashboard/puskesmas', {
    params: { bulan: periode.value.bulan, tahun: periode.value.tahun },
  })
  rows.value = data.data
  loading.value = false
}

watch(periode, load, { immediate: true, deep: true })
</script>

<template>
  <div class="space-y-5">
    <RouterLink to="/dinas/dashboard" class="inline-flex items-center gap-1 text-sm text-brand-700 hover:underline">
      &larr; Kembali ke Beranda
    </RouterLink>

    <div class="bg-brand-700 rounded-xl px-6 py-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-xl font-bold text-white">Daftar Puskesmas</h1>
        <p class="text-brand-100 text-sm mt-0.5">Status pengisian periode {{ formatPeriode(periode.bulan, periode.tahun) }}</p>
      </div>
      <div class="flex flex-col sm:flex-row sm:items-center gap-2">
        <PeriodeSelect v-model="periode" class="w-full sm:w-auto" />
        <RouterLink
          to="/dinas/puskesmas/baru"
          class="inline-flex items-center justify-center gap-1.5 bg-white hover:bg-brand-50 text-brand-700 text-sm font-medium px-4 py-2.5 rounded-lg whitespace-nowrap transition w-full sm:w-auto"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Tambah Puskesmas
        </RouterLink>
      </div>
    </div>

    <input
      v-model="search"
      type="text"
      placeholder="Cari nama puskesmas..."
      class="w-full sm:w-72 text-sm border border-gray-300 rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
    />

    <div v-if="loading" class="text-sm text-gray-400">Memuat...</div>

    <div v-else class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-gray-500 border-b border-gray-200 bg-gray-50">
            <th class="px-2.5 sm:px-4 py-3 font-medium">Puskesmas</th>
            <th class="px-1.5 sm:px-4 py-3 font-medium">Kuesioner</th>
            <th class="px-1.5 sm:px-4 py-3 font-medium">Observasi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="r in filtered" :key="r.id" class="hover:bg-brand-50/60 transition">
            <td class="px-2.5 sm:px-4 py-3">
              <RouterLink :to="`/dinas/puskesmas/${r.id}`" class="text-[15px] font-medium text-brand-700 hover:underline">
                {{ r.nama }}
              </RouterLink>
              <p v-if="r.kepala_puskesmas" class="text-xs text-gray-400">{{ r.kepala_puskesmas }}</p>
            </td>
            <td class="px-1.5 sm:px-4 py-3">
              <StatusPengisianBadge
                :sudah-isi="r.kuesioner_sudah_isi"
                :periode-bulan="periode.bulan"
                :periode-tahun="periode.tahun"
              />
            </td>
            <td class="px-1.5 sm:px-4 py-3">
              <StatusPengisianBadge
                :sudah-isi="r.observasi_sudah_isi"
                :periode-bulan="periode.bulan"
                :periode-tahun="periode.tahun"
              />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import api from '../../lib/api'
import { formatPeriode } from '../../lib/periode'
import PeriodeSelect from '../../components/PeriodeSelect.vue'

interface QuestionnaireRecapItem {
  kategori_kode: string
  kategori: string
  nomor: number
  pertanyaan: string
  ya: number
  tidak: number
  total_isi: number
}

interface ObservationRecapItem {
  kategori_kode: string
  kategori: string
  nomor: number
  item_teks: string
  skala_kondisi: 'baik_buruk' | 'baik_rusak_ringan_rusak_berat'
  ada: number
  tidak_ada: number
  kondisi: Record<string, number>
  total_isi: number
}

const kondisiLabel: Record<string, string> = {
  baik: 'Baik',
  buruk: 'Buruk',
  rusak_ringan: 'Rusak Ringan',
  rusak_berat: 'Rusak Berat',
}

const loading = ref(true)
const periode = ref({ bulan: new Date().getMonth() + 1, tahun: new Date().getFullYear() })
const jumlahKuesioner = ref(0)
const jumlahObservasi = ref(0)
const kuesioner = ref<QuestionnaireRecapItem[]>([])
const observasi = ref<ObservationRecapItem[]>([])
const tab = ref<'kuesioner' | 'observasi'>('kuesioner')

function groupByKategori<T extends { kategori_kode: string; kategori: string }>(items: T[]) {
  const map = new Map<string, { kategori: string; items: T[] }>()
  for (const item of items) {
    if (!map.has(item.kategori_kode)) map.set(item.kategori_kode, { kategori: item.kategori, items: [] })
    map.get(item.kategori_kode)!.items.push(item)
  }
  return [...map.entries()].sort(([a], [b]) => a.localeCompare(b))
}

const kuesionerGrouped = computed(() => groupByKategori(kuesioner.value))
const observasiGrouped = computed(() => groupByKategori(observasi.value))

async function load() {
  loading.value = true
  const { data } = await api.get('/api/dashboard/recap', {
    params: { bulan: periode.value.bulan, tahun: periode.value.tahun },
  })
  jumlahKuesioner.value = data.jumlah_puskesmas_isi_kuesioner
  jumlahObservasi.value = data.jumlah_puskesmas_isi_observasi
  kuesioner.value = data.kuesioner
  observasi.value = data.observasi
  loading.value = false
}

watch(periode, load, { immediate: true, deep: true })
</script>

<template>
  <div class="space-y-5">
    <div class="bg-brand-700 rounded-xl px-6 py-5 flex items-center justify-between flex-wrap gap-3">
      <div>
        <h1 class="text-xl font-bold text-white">Rekap Jawaban Seluruh Puskesmas</h1>
        <p class="text-brand-100 text-sm mt-0.5">Periode {{ formatPeriode(periode.bulan, periode.tahun) }}</p>
      </div>
      <PeriodeSelect v-model="periode" />
    </div>

    <div v-if="loading" class="text-sm text-gray-400">Memuat...</div>

    <template v-else>
      <p class="text-sm text-gray-500">
        <span class="font-medium text-gray-700">{{ jumlahKuesioner }}</span> puskesmas mengisi kuesioner &middot;
        <span class="font-medium text-gray-700">{{ jumlahObservasi }}</span> puskesmas mengisi observasi pada periode ini.
      </p>

      <div class="flex gap-1 border-b border-gray-200">
        <button
          class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition"
          :class="tab === 'kuesioner' ? 'border-brand-600 text-brand-700' : 'border-transparent text-gray-500 hover:text-gray-700'"
          @click="tab = 'kuesioner'"
        >
          Kuesioner K3
        </button>
        <button
          class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition"
          :class="tab === 'observasi' ? 'border-brand-600 text-brand-700' : 'border-transparent text-gray-500 hover:text-gray-700'"
          @click="tab = 'observasi'"
        >
          Observasi Sarana Prasarana
        </button>
      </div>

      <div v-if="tab === 'kuesioner'" class="space-y-6">
        <p v-if="jumlahKuesioner === 0" class="text-sm text-gray-400">Belum ada puskesmas yang mengisi periode ini.</p>

        <section v-for="[kode, group] in kuesionerGrouped" :key="kode" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
          <h2 class="px-4 py-3 border-b border-gray-200 bg-brand-50 font-semibold text-sm text-brand-800">{{ kode }}. {{ group.kategori }}</h2>
          <ul class="divide-y divide-gray-100">
            <li v-for="item in group.items" :key="item.nomor" class="px-4 py-3 space-y-1.5">
              <p class="text-sm text-gray-700">{{ item.nomor }}. {{ item.pertanyaan }}</p>
              <div v-if="item.total_isi > 0" class="flex items-center gap-2">
                <div class="flex-1 h-2 rounded-full bg-gray-100 overflow-hidden flex">
                  <div class="h-full bg-green-500" :style="{ width: `${(item.ya / item.total_isi) * 100}%` }" />
                  <div class="h-full bg-red-400" :style="{ width: `${(item.tidak / item.total_isi) * 100}%` }" />
                </div>
                <span class="text-xs text-gray-400 whitespace-nowrap">{{ item.ya }} Ya &middot; {{ item.tidak }} Tidak</span>
              </div>
              <p v-else class="text-xs text-gray-300">Belum ada data</p>
            </li>
          </ul>
        </section>
      </div>

      <div v-else class="space-y-6">
        <p v-if="jumlahObservasi === 0" class="text-sm text-gray-400">Belum ada puskesmas yang mengisi periode ini.</p>

        <section v-for="[kode, group] in observasiGrouped" :key="kode" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
          <h2 class="px-4 py-3 border-b border-gray-200 bg-brand-50 font-semibold text-sm text-brand-800">{{ kode }}. {{ group.kategori }}</h2>
          <ul class="divide-y divide-gray-100">
            <li v-for="item in group.items" :key="item.nomor" class="px-4 py-3 space-y-1.5">
              <p class="text-sm text-gray-700">{{ item.nomor }}. {{ item.item_teks }}</p>
              <div v-if="item.total_isi > 0" class="flex items-center gap-2">
                <div class="flex-1 h-2 rounded-full bg-gray-100 overflow-hidden flex">
                  <div class="h-full bg-green-500" :style="{ width: `${(item.ada / item.total_isi) * 100}%` }" />
                  <div class="h-full bg-red-400" :style="{ width: `${(item.tidak_ada / item.total_isi) * 100}%` }" />
                </div>
                <span class="text-xs text-gray-400 whitespace-nowrap">{{ item.ada }} Ada &middot; {{ item.tidak_ada }} Tidak Ada</span>
              </div>
              <div v-if="Object.keys(item.kondisi).length > 0" class="flex flex-wrap gap-1">
                <span
                  v-for="(count, kondisi) in item.kondisi"
                  :key="kondisi"
                  class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600"
                >
                  {{ kondisiLabel[kondisi] ?? kondisi }}: {{ count }}
                </span>
              </div>
              <p v-if="item.total_isi === 0" class="text-xs text-gray-300">Belum ada data</p>
            </li>
          </ul>
        </section>
      </div>
    </template>
  </div>
</template>

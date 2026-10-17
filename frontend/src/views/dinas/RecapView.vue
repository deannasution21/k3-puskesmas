<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import api from '../../lib/api'
import { formatPeriode, useQueryPeriode } from '../../lib/periode'
import PeriodeSelect from '../../components/PeriodeSelect.vue'
import Modal from '../../components/Modal.vue'
import StatusPengisianBadge from '../../components/StatusPengisianBadge.vue'
import { kategoriBadgeClasses, kategoriBarClasses, kategoriOrder, type PenilaianRingkasan } from '../../lib/penilaian'
import { formatFileSize } from '../../lib/attachments'
import {
  buildInsights,
  overallLevelFromInsights,
  overallBadgeFromLevel,
  insightLevelIcon,
  insightLevelClasses,
  type QuestionnaireRecapItem,
  type ObservationRecapItem,
} from '../../lib/insights'

interface PuskesmasRow {
  id: number
  nama: string
  kepala_puskesmas: string | null
  kuesioner_sudah_isi: boolean
  observasi_sudah_isi: boolean
}

const kondisiLabel: Record<string, string> = {
  baik: 'Baik',
  buruk: 'Buruk',
  rusak_ringan: 'Rusak Ringan',
  rusak_berat: 'Rusak Berat',
}

const kondisiClasses: Record<string, string> = {
  baik: 'bg-green-100 text-green-700',
  buruk: 'bg-red-100 text-red-700',
  rusak_ringan: 'bg-amber-100 text-amber-700',
  rusak_berat: 'bg-red-200 text-red-800',
}

const loading = ref(true)
const periode = useQueryPeriode()
const totalPuskesmas = ref(0)
const jumlahKuesioner = ref(0)
const jumlahObservasi = ref(0)
const kuesioner = ref<QuestionnaireRecapItem[]>([])
const observasi = ref<ObservationRecapItem[]>([])
const puskesmasRows = ref<PuskesmasRow[]>([])
const penilaian = ref<PenilaianRingkasan | null>(null)
const kategoriLabelShort: Record<string, string> = {
  tidak_memenuhi: 'Tidak Memenuhi',
  perlu_perbaikan_mayor: 'Perlu Perbaikan Mayor',
  memenuhi_sebagian: 'Memenuhi Sebagian',
  memenuhi_standar: 'Memenuhi Standar',
  unggul: 'Unggul',
}
const penilaianKategoriOpen = ref<string | null>(null)
const penilaianKategoriList = computed(() => {
  if (!penilaian.value || !penilaianKategoriOpen.value) return []
  return penilaian.value.list
    .filter((p) => p.kategori_key === penilaianKategoriOpen.value)
    .sort((a, b) => b.persen - a.persen)
})
const tab = ref<'kuesioner' | 'observasi'>('kuesioner')
const showBelumKuesioner = ref(false)
const showBelumObservasi = ref(false)

interface ItemDetailAttachment {
  id: number
  original_name: string
  mime_type: string
  size: number
}

interface ItemDetailRow {
  id: number
  nama: string
  kepala_puskesmas: string | null
  jawaban?: 'ya' | 'tidak' | null
  ada?: 'ada' | 'tidak_ada' | null
  kondisi?: string | null
  lampiran: ItemDetailAttachment[]
}

function isImageMime(mime: string) {
  return mime.startsWith('image/')
}

const itemDetailOpen = ref(false)
const itemDetailLoading = ref(false)
const itemDetailKind = ref<'kuesioner' | 'observasi'>('kuesioner')
const itemDetailTitle = ref('')
const itemDetailKategori = ref('')
const itemDetailRows = ref<ItemDetailRow[]>([])

const itemDetailGroups = computed(() => {
  if (itemDetailKind.value === 'kuesioner') {
    return [
      { key: 'ya', label: 'Menjawab Ya', color: 'text-green-700', dot: 'bg-green-500', tile: 'bg-green-50 border-green-100', rows: itemDetailRows.value.filter((r) => r.jawaban === 'ya') },
      { key: 'tidak', label: 'Menjawab Tidak', color: 'text-red-600', dot: 'bg-red-400', tile: 'bg-red-50 border-red-100', rows: itemDetailRows.value.filter((r) => r.jawaban === 'tidak') },
      { key: 'belum', label: 'Belum Menjawab', color: 'text-gray-500', dot: 'bg-gray-300', tile: 'bg-gray-50 border-gray-200', rows: itemDetailRows.value.filter((r) => !r.jawaban) },
    ]
  }
  return [
    { key: 'ada', label: 'Ada', color: 'text-green-700', dot: 'bg-green-500', tile: 'bg-green-50 border-green-100', rows: itemDetailRows.value.filter((r) => r.ada === 'ada') },
    { key: 'tidak_ada', label: 'Tidak Ada', color: 'text-red-600', dot: 'bg-red-400', tile: 'bg-red-50 border-red-100', rows: itemDetailRows.value.filter((r) => r.ada === 'tidak_ada') },
    { key: 'belum', label: 'Belum Mengisi', color: 'text-gray-500', dot: 'bg-gray-300', tile: 'bg-gray-50 border-gray-200', rows: itemDetailRows.value.filter((r) => !r.ada) },
  ]
})

async function openKuesionerItemDetail(item: QuestionnaireRecapItem) {
  itemDetailKind.value = 'kuesioner'
  itemDetailTitle.value = `${item.nomor}. ${item.pertanyaan}`
  itemDetailKategori.value = `${item.kategori_kode}. ${item.kategori}`
  itemDetailOpen.value = true
  itemDetailLoading.value = true
  itemDetailRows.value = []
  try {
    const { data } = await api.get(`/api/dashboard/recap/kuesioner/${item.id}/puskesmas`, {
      params: { bulan: periode.value.bulan, tahun: periode.value.tahun },
    })
    itemDetailRows.value = data.data
  } finally {
    itemDetailLoading.value = false
  }
}

async function openObservasiItemDetail(item: ObservationRecapItem) {
  itemDetailKind.value = 'observasi'
  itemDetailTitle.value = `${item.nomor}. ${item.item_teks}`
  itemDetailKategori.value = `${item.kategori_kode}. ${item.kategori}`
  itemDetailOpen.value = true
  itemDetailLoading.value = true
  itemDetailRows.value = []
  try {
    const { data } = await api.get(`/api/dashboard/recap/observasi/${item.id}/puskesmas`, {
      params: { bulan: periode.value.bulan, tahun: periode.value.tahun },
    })
    itemDetailRows.value = data.data
  } finally {
    itemDetailLoading.value = false
  }
}

const belumKuesionerList = computed(() => puskesmasRows.value.filter((p) => !p.kuesioner_sudah_isi))
const belumObservasiList = computed(() => puskesmasRows.value.filter((p) => !p.observasi_sudah_isi))

const insights = computed(() =>
  buildInsights({
    totalPuskesmas: totalPuskesmas.value,
    jumlahKuesioner: jumlahKuesioner.value,
    jumlahObservasi: jumlahObservasi.value,
    kuesioner: kuesioner.value,
    observasi: observasi.value,
  }),
)

const overallLevel = computed(() => overallLevelFromInsights(insights.value, jumlahKuesioner.value, jumlahObservasi.value))
const overallBadge = computed(() => overallBadgeFromLevel(overallLevel.value))
const levelIcon = insightLevelIcon
const levelClasses = insightLevelClasses

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
  const [recapRes, puskesmasRes, summaryRes] = await Promise.all([
    api.get('/api/dashboard/recap', { params: { bulan: periode.value.bulan, tahun: periode.value.tahun } }),
    api.get('/api/dashboard/puskesmas', { params: { bulan: periode.value.bulan, tahun: periode.value.tahun } }),
    api.get('/api/dashboard/summary', { params: { bulan: periode.value.bulan, tahun: periode.value.tahun } }),
  ])
  const data = recapRes.data
  totalPuskesmas.value = data.total_puskesmas
  jumlahKuesioner.value = data.jumlah_puskesmas_isi_kuesioner
  jumlahObservasi.value = data.jumlah_puskesmas_isi_observasi
  kuesioner.value = data.kuesioner
  observasi.value = data.observasi
  puskesmasRows.value = puskesmasRes.data.data
  penilaian.value = summaryRes.data.penilaian
  loading.value = false
}

watch(periode, load, { immediate: true, deep: true })
</script>

<template>
  <div class="space-y-5">
    <RouterLink to="/dinas/dashboard" class="inline-flex items-center gap-1 text-sm text-brand-700 hover:underline">
      &larr; Kembali ke Beranda
    </RouterLink>

    <div class="bg-brand-700 rounded-xl px-6 py-5 flex items-center justify-between flex-wrap gap-3">
      <div>
        <h1 class="text-xl font-bold text-white">Rekap Jawaban Seluruh Puskesmas</h1>
        <p class="text-brand-100 text-sm mt-0.5">
          Periode
          <span class="inline-block bg-white/15 text-white font-semibold px-2 py-0.5 rounded ml-1">{{ formatPeriode(periode.bulan, periode.tahun) }}</span>
        </p>
      </div>
      <PeriodeSelect v-model="periode" />
    </div>

    <div v-if="loading" class="text-sm text-gray-400">Memuat...</div>

    <template v-else>
      <div class="grid sm:grid-cols-2 gap-5">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-green-100 text-green-700 flex items-center justify-center shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500">Puskesmas Mengisi Kuesioner</p>
              <p class="text-3xl font-bold text-gray-900">{{ jumlahKuesioner }}</p>
            </div>
          </div>
          <div v-if="belumKuesionerList.length > 0" class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between gap-2">
            <p class="text-sm text-red-600 font-medium">{{ belumKuesionerList.length }} belum/tidak mengisi</p>
            <button
              type="button"
              class="text-sm font-medium text-brand-700 hover:underline whitespace-nowrap"
              @click="showBelumKuesioner = true"
            >
              Lihat daftar
            </button>
          </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-green-100 text-green-700 flex items-center justify-center shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.5 6a7.5 7.5 0 100 15 7.5 7.5 0 000-15zM10.5 6a7.5 7.5 0 000 15m9-1.5l-4.5-4.5" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500">Puskesmas Mengisi Observasi</p>
              <p class="text-3xl font-bold text-gray-900">{{ jumlahObservasi }}</p>
            </div>
          </div>
          <div v-if="belumObservasiList.length > 0" class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between gap-2">
            <p class="text-sm text-red-600 font-medium">{{ belumObservasiList.length }} belum/tidak mengisi</p>
            <button
              type="button"
              class="text-sm font-medium text-brand-700 hover:underline whitespace-nowrap"
              @click="showBelumObservasi = true"
            >
              Lihat daftar
            </button>
          </div>
        </div>
      </div>

      <div v-if="penilaian" class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between flex-wrap gap-3">
          <div>
            <h2 class="font-semibold text-gray-900">Penilaian K3 Puskesmas</h2>
            <p class="text-xs text-gray-400 mt-0.5">Rata-rata skor seluruh puskesmas periode ini</p>
          </div>
          <span class="text-sm font-semibold px-3 py-1.5 rounded-full whitespace-nowrap" :class="kategoriBadgeClasses[penilaian.kategori_key]">
            Rata-rata: {{ penilaian.kategori_label }}
          </span>
        </div>

        <div class="mt-4 flex items-center gap-4">
          <span class="text-3xl font-bold text-gray-900">{{ penilaian.rata_rata }}%</span>
          <div class="flex-1 h-3 rounded-full bg-gray-100 overflow-hidden">
            <div class="h-full rounded-full transition-all" :class="kategoriBarClasses[penilaian.kategori_key]" :style="{ width: `${Math.min(100, penilaian.rata_rata)}%` }" />
          </div>
        </div>

        <div class="mt-4 pt-4 border-t border-gray-100 flex flex-wrap gap-2">
          <button
            v-for="key in kategoriOrder"
            :key="key"
            type="button"
            class="text-xs font-medium px-2.5 py-1 rounded-full whitespace-nowrap hover:opacity-80 transition disabled:opacity-50 disabled:cursor-not-allowed"
            :class="kategoriBadgeClasses[key]"
            :disabled="penilaian.distribusi[key] === 0"
            @click="penilaianKategoriOpen = key"
          >
            {{ kategoriLabelShort[key] }}: {{ penilaian.distribusi[key] }}
          </button>
        </div>
      </div>

      <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between flex-wrap gap-2 mb-4">
          <div class="flex items-center gap-2">
            <div class="w-9 h-9 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />
              </svg>
            </div>
            <div>
              <h2 class="font-semibold text-gray-900">Ringkasan &amp; Rekomendasi</h2>
              <p class="text-xs text-gray-400">
                Dibuat otomatis dari data periode
                <span class="inline-block bg-brand-50 text-brand-700 font-semibold px-1.5 py-0.5 rounded ml-0.5">{{ formatPeriode(periode.bulan, periode.tahun) }}</span>
              </p>
            </div>
          </div>
          <span class="text-sm font-medium px-3 py-1.5 rounded-full whitespace-nowrap" :class="overallBadge.classes">
            {{ overallBadge.label }}
          </span>
        </div>

        <p v-if="overallLevel === 'empty'" class="text-sm text-gray-400">
          Belum ada puskesmas yang mengisi kuesioner maupun observasi pada periode ini, sehingga ringkasan belum dapat dibuat.
        </p>
        <p v-else-if="insights.length === 0" class="text-sm text-green-700">
          Semua puskesmas sudah mengisi dan tidak ditemukan temuan bermasalah yang menonjol pada periode ini. Pertahankan kondisi ini.
        </p>
        <ul v-else class="space-y-2.5">
          <li v-for="(insight, idx) in insights" :key="idx" class="flex items-start gap-2.5">
            <span class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 mt-0.5" :class="levelClasses[insight.level]">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="levelIcon[insight.level]" />
              </svg>
            </span>
            <p class="text-sm text-gray-700 leading-relaxed">{{ insight.text }}</p>
          </li>
        </ul>
      </div>

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
              <div v-if="item.total_isi > 0" class="flex items-center gap-3">
                <div class="flex-1 h-2.5 rounded-full bg-gray-100 overflow-hidden flex">
                  <div class="h-full bg-green-500" :style="{ width: `${(item.ya / item.total_isi) * 100}%` }" />
                  <div class="h-full bg-red-400" :style="{ width: `${(item.tidak / item.total_isi) * 100}%` }" />
                </div>
                <span class="text-sm font-medium whitespace-nowrap">
                  <span class="inline-flex items-center gap-1 text-green-700">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>{{ item.ya }} Ya
                  </span>
                  <span class="text-gray-300 mx-1.5">&middot;</span>
                  <span class="inline-flex items-center gap-1 text-red-600">
                    <span class="w-2 h-2 rounded-full bg-red-400"></span>{{ item.tidak }} Tidak
                  </span>
                </span>
              </div>
              <p v-else class="text-xs text-gray-300">Belum ada data</p>
              <button
                type="button"
                class="text-xs font-medium text-brand-700 hover:underline inline-flex items-center gap-0.5"
                @click="openKuesionerItemDetail(item)"
              >
                Lihat per puskesmas
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
              </button>
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
              <div v-if="item.total_isi > 0" class="flex items-center gap-3">
                <div class="flex-1 h-2.5 rounded-full bg-gray-100 overflow-hidden flex">
                  <div class="h-full bg-green-500" :style="{ width: `${(item.ada / item.total_isi) * 100}%` }" />
                  <div class="h-full bg-red-400" :style="{ width: `${(item.tidak_ada / item.total_isi) * 100}%` }" />
                </div>
                <span class="text-sm font-medium whitespace-nowrap">
                  <span class="inline-flex items-center gap-1 text-green-700">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>{{ item.ada }} Ada
                  </span>
                  <span class="text-gray-300 mx-1.5">&middot;</span>
                  <span class="inline-flex items-center gap-1 text-red-600">
                    <span class="w-2 h-2 rounded-full bg-red-400"></span>{{ item.tidak_ada }} Tidak Ada
                  </span>
                </span>
              </div>
              <div v-if="Object.keys(item.kondisi).length > 0" class="flex flex-wrap gap-2">
                <span
                  v-for="(count, kondisi) in item.kondisi"
                  :key="kondisi"
                  class="text-sm font-medium px-3 py-1.5 rounded-full"
                  :class="kondisiClasses[kondisi] ?? 'bg-gray-100 text-gray-600'"
                >
                  {{ kondisiLabel[kondisi] ?? kondisi }}: {{ count }}
                </span>
              </div>
              <p v-if="item.total_isi === 0" class="text-xs text-gray-300">Belum ada data</p>
              <button
                type="button"
                class="text-xs font-medium text-brand-700 hover:underline inline-flex items-center gap-0.5"
                @click="openObservasiItemDetail(item)"
              >
                Lihat per puskesmas
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
              </button>
            </li>
          </ul>
        </section>
      </div>
    </template>

    <Modal v-if="showBelumKuesioner" title="Belum/Tidak Mengisi Kuesioner K3" size="md" @close="showBelumKuesioner = false">
      <p class="text-sm text-gray-500 mb-3">
        Periode
        <span class="inline-block bg-brand-50 text-brand-700 font-semibold px-1.5 py-0.5 rounded ml-0.5">{{ formatPeriode(periode.bulan, periode.tahun) }}</span>
        &middot; {{ belumKuesionerList.length }} puskesmas
      </p>
      <ul class="divide-y divide-gray-100 max-h-80 overflow-y-auto -mx-5 px-5">
        <li v-for="p in belumKuesionerList" :key="p.id" class="py-2.5 flex items-center justify-between gap-3 flex-wrap">
          <div>
            <RouterLink
              :to="{ path: `/dinas/puskesmas/${p.id}`, query: { from: 'rekap', bulan: periode.bulan, tahun: periode.tahun } }"
              class="text-sm font-medium text-brand-700 hover:underline"
              @click="showBelumKuesioner = false"
            >
              {{ p.nama }}
            </RouterLink>
            <p v-if="p.kepala_puskesmas" class="text-xs text-gray-400">{{ p.kepala_puskesmas }}</p>
          </div>
          <StatusPengisianBadge :sudah-isi="false" :periode-bulan="periode.bulan" :periode-tahun="periode.tahun" />
        </li>
      </ul>
    </Modal>

    <Modal v-if="showBelumObservasi" title="Belum/Tidak Mengisi Observasi" size="md" @close="showBelumObservasi = false">
      <p class="text-sm text-gray-500 mb-3">
        Periode
        <span class="inline-block bg-brand-50 text-brand-700 font-semibold px-1.5 py-0.5 rounded ml-0.5">{{ formatPeriode(periode.bulan, periode.tahun) }}</span>
        &middot; {{ belumObservasiList.length }} puskesmas
      </p>
      <ul class="divide-y divide-gray-100 max-h-80 overflow-y-auto -mx-5 px-5">
        <li v-for="p in belumObservasiList" :key="p.id" class="py-2.5 flex items-center justify-between gap-3 flex-wrap">
          <div>
            <RouterLink
              :to="{ path: `/dinas/puskesmas/${p.id}`, query: { from: 'rekap', bulan: periode.bulan, tahun: periode.tahun } }"
              class="text-sm font-medium text-brand-700 hover:underline"
              @click="showBelumObservasi = false"
            >
              {{ p.nama }}
            </RouterLink>
            <p v-if="p.kepala_puskesmas" class="text-xs text-gray-400">{{ p.kepala_puskesmas }}</p>
          </div>
          <StatusPengisianBadge :sudah-isi="false" :periode-bulan="periode.bulan" :periode-tahun="periode.tahun" />
        </li>
      </ul>
    </Modal>

    <Modal v-if="itemDetailOpen" title="Detail Jawaban per Puskesmas" size="lg" @close="itemDetailOpen = false">
      <p class="text-xs text-gray-400 mb-0.5">{{ itemDetailKategori }}</p>
      <p class="text-sm font-medium text-gray-800 mb-3">{{ itemDetailTitle }}</p>

      <div v-if="itemDetailLoading" class="text-sm text-gray-400 py-4">Memuat...</div>
      <template v-else>
        <div class="grid grid-cols-3 gap-2.5 mb-4">
          <div v-for="group in itemDetailGroups" :key="group.key" class="rounded-lg border p-3 text-center" :class="group.tile">
            <p class="text-2xl font-bold" :class="group.color">{{ group.rows.length }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ group.label }}</p>
            <p class="text-[11px] text-gray-400 mt-0.5">
              {{ itemDetailRows.length > 0 ? Math.round((group.rows.length / itemDetailRows.length) * 100) : 0 }}%
            </p>
          </div>
        </div>

        <div class="space-y-4 max-h-96 overflow-y-auto -mx-5 px-5">
        <div v-for="group in itemDetailGroups" :key="group.key">
          <p class="text-xs font-semibold uppercase tracking-wide mb-1.5 flex items-center gap-1.5" :class="group.color">
            <span class="w-2 h-2 rounded-full" :class="group.dot"></span>
            {{ group.label }} ({{ group.rows.length }})
          </p>
          <p v-if="group.rows.length === 0" class="text-xs text-gray-300 mb-2">Tidak ada</p>
          <ul v-else class="divide-y divide-gray-100 border border-gray-100 rounded-lg mb-2">
            <li v-for="p in group.rows" :key="p.id" class="px-3 py-2">
              <div class="flex items-center justify-between gap-3 flex-wrap">
                <div>
                  <RouterLink
                    :to="{ path: `/dinas/puskesmas/${p.id}`, query: { from: 'rekap', bulan: periode.bulan, tahun: periode.tahun } }"
                    class="text-sm font-medium text-brand-700 hover:underline"
                    @click="itemDetailOpen = false"
                  >
                    {{ p.nama }}
                  </RouterLink>
                  <p v-if="p.kepala_puskesmas" class="text-xs text-gray-400">{{ p.kepala_puskesmas }}</p>
                </div>
                <span
                  v-if="itemDetailKind === 'observasi' && p.ada === 'ada' && p.kondisi"
                  class="text-xs font-medium px-2.5 py-1 rounded-full whitespace-nowrap"
                  :class="kondisiClasses[p.kondisi] ?? 'bg-gray-100 text-gray-600'"
                >
                  {{ kondisiLabel[p.kondisi] ?? p.kondisi }}
                </span>
              </div>

              <div v-if="p.lampiran.length > 0" class="flex flex-wrap gap-1.5 mt-1.5">
                <a
                  v-for="file in p.lampiran"
                  :key="file.id"
                  :href="`/api/${itemDetailKind === 'kuesioner' ? 'questionnaire' : 'observation'}/attachments/${file.id}/download`"
                  target="_blank"
                  class="inline-flex items-center gap-1 bg-gray-100 hover:bg-gray-200 border border-gray-200 rounded-md pl-1.5 pr-2 py-1 text-xs text-gray-700 max-w-[200px] transition"
                  :title="file.original_name"
                >
                  <svg v-if="isImageMime(file.mime_type)" xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 4.5h18A1.5 1.5 0 0122.5 6v12a1.5 1.5 0 01-1.5 1.5H3A1.5 1.5 0 011.5 18V6A1.5 1.5 0 013 4.5zm13.5 6a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                  </svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                  </svg>
                  <span class="truncate">{{ file.original_name }}</span>
                  <span class="text-gray-400 shrink-0">({{ formatFileSize(file.size) }})</span>
                </a>
              </div>
            </li>
          </ul>
        </div>
      </div>
      </template>
    </Modal>

    <Modal
      v-if="penilaianKategoriOpen"
      :title="`Kategori: ${kategoriLabelShort[penilaianKategoriOpen]}`"
      size="md"
      :header-class="kategoriBadgeClasses[penilaianKategoriOpen]"
      @close="penilaianKategoriOpen = null"
    >
      <p class="text-sm text-gray-500 mb-3">
        Periode
        <span class="inline-block bg-brand-50 text-brand-700 font-semibold px-1.5 py-0.5 rounded ml-0.5">{{ formatPeriode(periode.bulan, periode.tahun) }}</span>
        &middot; {{ penilaianKategoriList.length }} puskesmas
      </p>
      <ul class="divide-y divide-gray-100 max-h-80 overflow-y-auto -mx-5 px-5">
        <li v-for="p in penilaianKategoriList" :key="p.puskesmas_id" class="py-2.5 flex items-center justify-between gap-3 flex-wrap">
          <RouterLink
            :to="{ path: `/dinas/puskesmas/${p.puskesmas_id}`, query: { from: 'rekap', bulan: periode.bulan, tahun: periode.tahun } }"
            class="text-sm font-medium text-brand-700 hover:underline"
            @click="penilaianKategoriOpen = null"
          >
            {{ p.nama }}
          </RouterLink>
          <span class="text-sm font-semibold px-2.5 py-1 rounded-full whitespace-nowrap" :class="kategoriBadgeClasses[p.kategori_key]">
            {{ p.persen }}%
          </span>
        </li>
      </ul>
    </Modal>
  </div>
</template>

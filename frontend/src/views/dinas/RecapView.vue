<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import api from '../../lib/api'
import { formatPeriode } from '../../lib/periode'
import PeriodeSelect from '../../components/PeriodeSelect.vue'
import Modal from '../../components/Modal.vue'
import StatusPengisianBadge from '../../components/StatusPengisianBadge.vue'
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
const periode = ref({ bulan: new Date().getMonth() + 1, tahun: new Date().getFullYear() })
const totalPuskesmas = ref(0)
const jumlahKuesioner = ref(0)
const jumlahObservasi = ref(0)
const kuesioner = ref<QuestionnaireRecapItem[]>([])
const observasi = ref<ObservationRecapItem[]>([])
const puskesmasRows = ref<PuskesmasRow[]>([])
const tab = ref<'kuesioner' | 'observasi'>('kuesioner')
const showBelumKuesioner = ref(false)
const showBelumObservasi = ref(false)

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
  const [recapRes, puskesmasRes] = await Promise.all([
    api.get('/api/dashboard/recap', { params: { bulan: periode.value.bulan, tahun: periode.value.tahun } }),
    api.get('/api/dashboard/puskesmas', { params: { bulan: periode.value.bulan, tahun: periode.value.tahun } }),
  ])
  const data = recapRes.data
  totalPuskesmas.value = data.total_puskesmas
  jumlahKuesioner.value = data.jumlah_puskesmas_isi_kuesioner
  jumlahObservasi.value = data.jumlah_puskesmas_isi_observasi
  kuesioner.value = data.kuesioner
  observasi.value = data.observasi
  puskesmasRows.value = puskesmasRes.data.data
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
              <p class="text-xs text-gray-400">Dibuat otomatis dari data periode {{ formatPeriode(periode.bulan, periode.tahun) }}</p>
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
            </li>
          </ul>
        </section>
      </div>
    </template>

    <Modal v-if="showBelumKuesioner" title="Belum/Tidak Mengisi Kuesioner K3" size="md" @close="showBelumKuesioner = false">
      <p class="text-sm text-gray-500 mb-3">
        Periode {{ formatPeriode(periode.bulan, periode.tahun) }} &middot; {{ belumKuesionerList.length }} puskesmas
      </p>
      <ul class="divide-y divide-gray-100 max-h-80 overflow-y-auto -mx-5 px-5">
        <li v-for="p in belumKuesionerList" :key="p.id" class="py-2.5 flex items-center justify-between gap-3 flex-wrap">
          <div>
            <RouterLink :to="`/dinas/puskesmas/${p.id}`" class="text-sm font-medium text-brand-700 hover:underline" @click="showBelumKuesioner = false">
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
        Periode {{ formatPeriode(periode.bulan, periode.tahun) }} &middot; {{ belumObservasiList.length }} puskesmas
      </p>
      <ul class="divide-y divide-gray-100 max-h-80 overflow-y-auto -mx-5 px-5">
        <li v-for="p in belumObservasiList" :key="p.id" class="py-2.5 flex items-center justify-between gap-3 flex-wrap">
          <div>
            <RouterLink :to="`/dinas/puskesmas/${p.id}`" class="text-sm font-medium text-brand-700 hover:underline" @click="showBelumObservasi = false">
              {{ p.nama }}
            </RouterLink>
            <p v-if="p.kepala_puskesmas" class="text-xs text-gray-400">{{ p.kepala_puskesmas }}</p>
          </div>
          <StatusPengisianBadge :sudah-isi="false" :periode-bulan="periode.bulan" :periode-tahun="periode.tahun" />
        </li>
      </ul>
    </Modal>
  </div>
</template>

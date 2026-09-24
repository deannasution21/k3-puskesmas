<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import api from '../../lib/api'
import { formatPeriode } from '../../lib/periode'
import PeriodeSelect from '../../components/PeriodeSelect.vue'
import DonutChart from '../../components/DonutChart.vue'

const loading = ref(true)
const summary = ref<any>(null)
const periode = ref({ bulan: new Date().getMonth() + 1, tahun: new Date().getFullYear() })

async function load() {
  loading.value = true
  const { data } = await api.get('/api/dashboard/summary', {
    params: { bulan: periode.value.bulan, tahun: periode.value.tahun },
  })
  summary.value = data
  loading.value = false
}

watch(periode, load, { immediate: true, deep: true })

const kuesionerPercent = computed(() =>
  summary.value?.total_puskesmas ? (summary.value.kuesioner_sudah_isi / summary.value.total_puskesmas) * 100 : 0,
)
const observasiPercent = computed(() =>
  summary.value?.total_puskesmas ? (summary.value.observasi_sudah_isi / summary.value.total_puskesmas) * 100 : 0,
)
</script>

<template>
  <div class="space-y-6">
    <div class="bg-brand-700 rounded-xl px-6 py-5 flex items-center justify-between flex-wrap gap-3">
      <div>
        <h1 class="text-xl font-bold text-white">Dashboard Dinas Ketenagakerjaan</h1>
        <p class="text-brand-100 text-sm mt-0.5">Pemantauan K3 seluruh Puskesmas Kota Pontianak</p>
      </div>
      <PeriodeSelect v-model="periode" />
    </div>

    <div v-if="loading" class="text-sm text-gray-400">Memuat...</div>

    <template v-else>
      <p class="text-sm text-gray-500">Menampilkan data periode <strong>{{ formatPeriode(summary.periode_bulan, summary.periode_tahun) }}</strong></p>

      <div class="grid md:grid-cols-3 gap-5">
        <div class="bg-white rounded-xl border border-gray-200 p-6 flex items-center gap-4">
          <div class="w-12 h-12 rounded-lg bg-brand-100 text-brand-700 flex items-center justify-center shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m3-3h.75m-.75 3h.75M6.75 3h10.5a1.5 1.5 0 011.5 1.5v15a1.5 1.5 0 01-1.5 1.5H6.75a1.5 1.5 0 01-1.5-1.5v-15a1.5 1.5 0 011.5-1.5z" />
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Total Puskesmas</p>
            <p class="text-3xl font-bold text-gray-900">{{ summary.total_puskesmas }}</p>
          </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 flex items-center gap-4">
          <div class="w-12 h-12 rounded-lg bg-green-100 text-green-700 flex items-center justify-center shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12.75L11.25 15 15 9.75m6 2.25a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Kuesioner Sudah Diisi</p>
            <p class="text-3xl font-bold text-gray-900">
              {{ summary.kuesioner_sudah_isi }}<span class="text-base font-normal text-gray-400">/{{ summary.total_puskesmas }}</span>
            </p>
          </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 flex items-center gap-4">
          <div class="w-12 h-12 rounded-lg bg-green-100 text-green-700 flex items-center justify-center shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15.75 15.75l-2.489-2.489m0 0a3.375 3.375 0 10-4.773-4.773 3.375 3.375 0 004.774 4.774zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Observasi Sudah Diisi</p>
            <p class="text-3xl font-bold text-gray-900">
              {{ summary.observasi_sudah_isi }}<span class="text-base font-normal text-gray-400">/{{ summary.total_puskesmas }}</span>
            </p>
          </div>
        </div>
      </div>

      <div class="grid md:grid-cols-2 gap-5">
        <div class="bg-white rounded-xl border border-gray-200 p-6 flex items-center gap-6">
          <DonutChart :percent="kuesionerPercent" color="#1a6838" track-color="#e5e7eb" :size="120" />
          <div>
            <h2 class="font-semibold text-gray-900">Kuesioner K3</h2>
            <p class="text-sm text-gray-500 mt-1">
              <span class="text-green-700 font-medium">{{ summary.kuesioner_sudah_isi }} sudah mengisi</span>
            </p>
            <p class="text-sm text-gray-500">
              <span class="text-red-500 font-medium">{{ summary.kuesioner_belum_isi }} belum mengisi</span>
            </p>
          </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 flex items-center gap-6">
          <DonutChart :percent="observasiPercent" color="#1a6838" track-color="#e5e7eb" :size="120" />
          <div>
            <h2 class="font-semibold text-gray-900">Observasi Sarana Prasarana</h2>
            <p class="text-sm text-gray-500 mt-1">
              <span class="text-green-700 font-medium">{{ summary.observasi_sudah_isi }} sudah mengisi</span>
            </p>
            <p class="text-sm text-gray-500">
              <span class="text-red-500 font-medium">{{ summary.observasi_belum_isi }} belum mengisi</span>
            </p>
          </div>
        </div>
      </div>

      <div class="flex flex-wrap gap-3">
        <RouterLink
          to="/dinas/puskesmas"
          class="inline-flex items-center gap-2 bg-white border border-gray-200 hover:border-brand-300 hover:bg-brand-50 rounded-lg px-4 py-2.5 text-sm font-medium text-gray-700 transition"
        >
          Lihat Daftar &amp; Status Puskesmas &rarr;
        </RouterLink>
        <RouterLink
          to="/dinas/rekap"
          class="inline-flex items-center gap-2 bg-white border border-gray-200 hover:border-brand-300 hover:bg-brand-50 rounded-lg px-4 py-2.5 text-sm font-medium text-gray-700 transition"
        >
          Lihat Rekap Jawaban &rarr;
        </RouterLink>
      </div>
    </template>
  </div>
</template>

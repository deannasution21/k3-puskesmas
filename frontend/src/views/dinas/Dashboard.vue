<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '../../lib/api'
import { formatPeriode } from '../../lib/periode'

const loading = ref(true)
const summary = ref<any>(null)

onMounted(async () => {
  const { data } = await api.get('/api/dashboard/summary')
  summary.value = data
  loading.value = false
})
</script>

<template>
  <div class="space-y-6">
    <h1 class="text-xl font-semibold text-gray-900">Dashboard Dinas Ketenagakerjaan</h1>

    <div v-if="loading" class="text-sm text-gray-400">Memuat...</div>

    <template v-else>
      <p class="text-sm text-gray-500">Periode {{ formatPeriode(summary.periode_bulan, summary.periode_tahun) }}</p>

      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg border border-gray-200 p-5">
          <p class="text-sm text-gray-500">Total Puskesmas</p>
          <p class="text-3xl font-semibold text-gray-900 mt-1">{{ summary.total_puskesmas }}</p>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-5">
          <p class="text-sm text-gray-500">Kuesioner Sudah Diisi</p>
          <p class="text-3xl font-semibold text-green-600 mt-1">
            {{ summary.kuesioner_sudah_isi }}<span class="text-base text-gray-400">/{{ summary.total_puskesmas }}</span>
          </p>
          <p class="text-xs text-yellow-600 mt-1">{{ summary.kuesioner_belum_isi }} belum mengisi</p>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-5">
          <p class="text-sm text-gray-500">Observasi Sudah Diisi</p>
          <p class="text-3xl font-semibold text-green-600 mt-1">
            {{ summary.observasi_sudah_isi }}<span class="text-base text-gray-400">/{{ summary.total_puskesmas }}</span>
          </p>
          <p class="text-xs text-yellow-600 mt-1">{{ summary.observasi_belum_isi }} belum mengisi</p>
        </div>
      </div>

      <RouterLink to="/dinas/puskesmas" class="inline-block text-sm text-blue-600 hover:underline">
        Lihat daftar & status seluruh puskesmas &rarr;
      </RouterLink>
    </template>
  </div>
</template>

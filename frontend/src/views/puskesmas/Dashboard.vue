<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '../../lib/api'
import { formatPeriode } from '../../lib/periode'
import { useAuthStore } from '../../stores/auth'

const auth = useAuthStore()
const loading = ref(true)
const periodeBulan = ref(0)
const periodeTahun = ref(0)
const kuesionerSudah = ref(false)
const observasiSudah = ref(false)

onMounted(async () => {
  const [q, o] = await Promise.all([
    api.get('/api/questionnaire/submissions/current'),
    api.get('/api/observation/submissions/current'),
  ])
  periodeBulan.value = q.data.periode_bulan
  periodeTahun.value = q.data.periode_tahun
  kuesionerSudah.value = q.data.submission !== null
  observasiSudah.value = o.data.submission !== null
  loading.value = false
})
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-xl font-semibold text-gray-900">Dashboard Puskesmas</h1>
      <p class="text-sm text-gray-500 mt-1">{{ auth.user?.puskesmas?.nama }}</p>
    </div>

    <div v-if="loading" class="text-sm text-gray-400">Memuat...</div>

    <template v-else>
      <p class="text-sm text-gray-600">Status pengisian periode {{ formatPeriode(periodeBulan, periodeTahun) }}:</p>

      <div class="grid sm:grid-cols-2 gap-4">
        <RouterLink
          to="/puskesmas/kuesioner"
          class="bg-white rounded-lg border border-gray-200 p-5 hover:border-blue-300 transition"
        >
          <div class="flex items-center justify-between">
            <h2 class="font-medium text-gray-900">Kuesioner K3</h2>
            <span
              class="text-xs px-2 py-0.5 rounded-full"
              :class="kuesionerSudah ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"
            >
              {{ kuesionerSudah ? 'Sudah diisi' : 'Belum diisi' }}
            </span>
          </div>
          <p class="text-sm text-gray-500 mt-2">Lampiran 6 — 21 pertanyaan</p>
        </RouterLink>

        <RouterLink
          to="/puskesmas/observasi"
          class="bg-white rounded-lg border border-gray-200 p-5 hover:border-blue-300 transition"
        >
          <div class="flex items-center justify-between">
            <h2 class="font-medium text-gray-900">Observasi Sarana Prasarana</h2>
            <span
              class="text-xs px-2 py-0.5 rounded-full"
              :class="observasiSudah ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"
            >
              {{ observasiSudah ? 'Sudah diisi' : 'Belum diisi' }}
            </span>
          </div>
          <p class="text-sm text-gray-500 mt-2">Lampiran 7 — 96 item observasi</p>
        </RouterLink>
      </div>
    </template>
  </div>
</template>

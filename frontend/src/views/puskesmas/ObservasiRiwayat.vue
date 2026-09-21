<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '../../lib/api'
import { formatPeriode, periodeKey } from '../../lib/periode'

interface Submission {
  id: number
  periode_bulan: number
  periode_tahun: number
  answers_count: number
}

const submissions = ref<Submission[]>([])
const loading = ref(true)

onMounted(async () => {
  const { data } = await api.get('/api/observation/submissions')
  submissions.value = data
  loading.value = false
})
</script>

<template>
  <div class="space-y-4">
    <div>
      <h1 class="text-xl font-semibold text-gray-900">Riwayat Observasi</h1>
      <RouterLink to="/puskesmas/observasi" class="text-sm text-blue-600 hover:underline">
        &larr; Kembali ke pengisian bulan ini
      </RouterLink>
    </div>

    <div v-if="loading" class="text-sm text-gray-400">Memuat...</div>
    <p v-else-if="submissions.length === 0" class="text-sm text-gray-400">Belum ada riwayat pengisian.</p>

    <div v-else class="bg-white rounded-lg border border-gray-200 divide-y divide-gray-100">
      <RouterLink
        v-for="s in submissions"
        :key="s.id"
        :to="`/puskesmas/observasi/riwayat/${periodeKey(s.periode_bulan, s.periode_tahun)}`"
        class="flex items-center justify-between px-4 py-3 hover:bg-gray-50"
      >
        <span class="text-sm text-gray-900">{{ formatPeriode(s.periode_bulan, s.periode_tahun) }}</span>
        <span class="text-xs text-gray-400">{{ s.answers_count }}/96 terisi</span>
      </RouterLink>
    </div>
  </div>
</template>

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
  const { data } = await api.get('/api/questionnaire/submissions')
  submissions.value = data
  loading.value = false
})
</script>

<template>
  <div class="space-y-4">
    <div>
      <h1 class="text-xl font-bold text-gray-900">Riwayat Kuesioner K3</h1>
      <RouterLink to="/puskesmas/kuesioner" class="inline-flex items-center gap-1 text-sm text-brand-700 hover:underline mt-1">
        &larr; Kembali ke pengisian bulan ini
      </RouterLink>
    </div>

    <div v-if="loading" class="text-sm text-gray-400">Memuat...</div>
    <p v-else-if="submissions.length === 0" class="text-sm text-gray-400">Belum ada riwayat pengisian.</p>

    <div v-else class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100 overflow-hidden">
      <RouterLink
        v-for="s in submissions"
        :key="s.id"
        :to="`/puskesmas/kuesioner/riwayat/${periodeKey(s.periode_bulan, s.periode_tahun)}`"
        class="flex items-center justify-between px-4 py-3.5 hover:bg-brand-50 transition"
      >
        <span class="text-[15px] text-gray-900">{{ formatPeriode(s.periode_bulan, s.periode_tahun) }}</span>
        <span class="text-sm text-gray-400">{{ s.answers_count }}/21 terisi</span>
      </RouterLink>
    </div>
  </div>
</template>

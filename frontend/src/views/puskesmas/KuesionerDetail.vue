<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import api from '../../lib/api'
import { formatPeriode } from '../../lib/periode'
import QuestionnaireForm from '../../components/QuestionnaireForm.vue'

const route = useRoute()
const items = ref<any[]>([])
const answers = ref<Record<number, 'ya' | 'tidak' | undefined>>({})
const periodeBulan = ref(0)
const periodeTahun = ref(0)
const loading = ref(true)

onMounted(async () => {
  const periode = route.params.periode as string
  const { data } = await api.get(`/api/questionnaire/submissions/${periode}`)
  periodeBulan.value = data.periode_bulan
  periodeTahun.value = data.periode_tahun

  const map: Record<number, 'ya' | 'tidak' | undefined> = {}
  const itemList: any[] = []
  for (const a of data.answers) {
    map[a.item_id] = a.jawaban
    itemList.push(a.item)
  }
  items.value = itemList.sort((a, b) => (a.kategori_kode + a.nomor).localeCompare(b.kategori_kode + b.nomor))
  answers.value = map
  loading.value = false
})
</script>

<template>
  <div class="space-y-4">
    <div>
      <h1 class="text-xl font-bold text-gray-900">Kuesioner K3 — {{ formatPeriode(periodeBulan, periodeTahun) }}</h1>
      <RouterLink to="/puskesmas/kuesioner/riwayat" class="inline-flex items-center gap-1 text-sm text-brand-700 hover:underline mt-1">
        &larr; Kembali ke riwayat
      </RouterLink>
    </div>

    <div v-if="loading" class="text-sm text-gray-400">Memuat...</div>
    <QuestionnaireForm v-else :items="items" v-model="answers" readonly />
  </div>
</template>

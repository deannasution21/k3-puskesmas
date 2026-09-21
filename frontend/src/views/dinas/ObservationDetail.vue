<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import api from '../../lib/api'
import { formatPeriode } from '../../lib/periode'
import ObservationForm, { type ObservationAnswer } from '../../components/ObservationForm.vue'

const route = useRoute()
const items = ref<any[]>([])
const answers = ref<Record<number, ObservationAnswer>>({})
const periodeBulan = ref(0)
const periodeTahun = ref(0)
const tanggalObservasi = ref<string | null>(null)
const loading = ref(true)

onMounted(async () => {
  const { id, periode } = route.params
  const { data } = await api.get(`/api/dashboard/puskesmas/${id}/observation/${periode}`)
  periodeBulan.value = data.periode_bulan
  periodeTahun.value = data.periode_tahun
  tanggalObservasi.value = data.tanggal_observasi?.slice(0, 10) ?? null

  const map: Record<number, ObservationAnswer> = {}
  const itemList: any[] = []
  for (const a of data.answers) {
    map[a.item_id] = { ada: a.ada, kondisi: a.kondisi, keterangan: a.keterangan }
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
      <h1 class="text-xl font-semibold text-gray-900">Observasi — {{ formatPeriode(periodeBulan, periodeTahun) }}</h1>
      <p v-if="tanggalObservasi" class="text-sm text-gray-500">Tanggal observasi: {{ tanggalObservasi }}</p>
      <RouterLink :to="`/dinas/puskesmas/${route.params.id}`" class="text-sm text-blue-600 hover:underline">
        &larr; Kembali ke detail puskesmas
      </RouterLink>
    </div>

    <div v-if="loading" class="text-sm text-gray-400">Memuat...</div>
    <ObservationForm v-else :items="items" v-model="answers" readonly />
  </div>
</template>

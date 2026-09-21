<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '../../lib/api'
import { formatPeriode } from '../../lib/periode'

interface Row {
  id: number
  nama: string
  alamat: string
  kuesioner_sudah_isi: boolean
  observasi_sudah_isi: boolean
}

const loading = ref(true)
const rows = ref<Row[]>([])
const periodeBulan = ref(0)
const periodeTahun = ref(0)
const search = ref('')

const filtered = computed(() =>
  rows.value.filter((r) => r.nama.toLowerCase().includes(search.value.toLowerCase())),
)

onMounted(async () => {
  const { data } = await api.get('/api/dashboard/puskesmas')
  rows.value = data.data
  periodeBulan.value = data.periode_bulan
  periodeTahun.value = data.periode_tahun
  loading.value = false
})
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between flex-wrap gap-2">
      <div>
        <h1 class="text-xl font-semibold text-gray-900">Daftar Puskesmas</h1>
        <p class="text-sm text-gray-500">Status pengisian periode {{ formatPeriode(periodeBulan, periodeTahun) }}</p>
      </div>
      <RouterLink
        to="/dinas/puskesmas/baru"
        class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-md"
      >
        + Tambah Puskesmas
      </RouterLink>
    </div>

    <input
      v-model="search"
      type="text"
      placeholder="Cari nama puskesmas..."
      class="w-full sm:w-72 text-sm border border-gray-300 rounded-md px-3 py-2"
    />

    <div v-if="loading" class="text-sm text-gray-400">Memuat...</div>

    <div v-else class="bg-white rounded-lg border border-gray-200 overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-gray-500 border-b border-gray-200">
            <th class="px-4 py-2 font-medium">Puskesmas</th>
            <th class="px-4 py-2 font-medium">Kuesioner</th>
            <th class="px-4 py-2 font-medium">Observasi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="r in filtered" :key="r.id" class="hover:bg-gray-50">
            <td class="px-4 py-2">
              <RouterLink :to="`/dinas/puskesmas/${r.id}`" class="text-blue-600 hover:underline">
                {{ r.nama }}
              </RouterLink>
            </td>
            <td class="px-4 py-2">
              <span
                class="text-xs px-2 py-0.5 rounded-full"
                :class="r.kuesioner_sudah_isi ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"
              >
                {{ r.kuesioner_sudah_isi ? 'Sudah' : 'Belum' }}
              </span>
            </td>
            <td class="px-4 py-2">
              <span
                class="text-xs px-2 py-0.5 rounded-full"
                :class="r.observasi_sudah_isi ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"
              >
                {{ r.observasi_sudah_isi ? 'Sudah' : 'Belum' }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

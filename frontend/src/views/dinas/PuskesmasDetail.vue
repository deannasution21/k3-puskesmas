<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import api from '../../lib/api'
import { formatPeriode, periodeKey } from '../../lib/periode'
import { extractErrorMessage } from '../../lib/errors'

const route = useRoute()
const router = useRouter()
const loading = ref(true)
const puskesmas = ref<any>(null)
const kuesionerHistory = ref<any[]>([])
const observasiHistory = ref<any[]>([])

const newPassword = ref('')
const resetting = ref(false)
const resetMessage = ref('')
const resetError = ref('')
const deleting = ref(false)

async function load() {
  const { data } = await api.get(`/api/dashboard/puskesmas/${route.params.id}`)
  puskesmas.value = data.puskesmas
  kuesionerHistory.value = data.kuesioner_history
  observasiHistory.value = data.observasi_history
  loading.value = false
}

async function resetPassword() {
  resetMessage.value = ''
  resetError.value = ''
  if (newPassword.value.length < 8) {
    resetError.value = 'Password minimal 8 karakter.'
    return
  }
  resetting.value = true
  try {
    await api.post(`/api/admin/puskesmas/${route.params.id}/reset-password`, { password: newPassword.value })
    resetMessage.value = 'Password berhasil diganti.'
    newPassword.value = ''
  } catch (e) {
    resetError.value = extractErrorMessage(e)
  } finally {
    resetting.value = false
  }
}

async function hapus() {
  if (!confirm(`Hapus ${puskesmas.value.nama}? Seluruh data pengisian & akun login puskesmas ini akan ikut terhapus permanen.`)) return
  deleting.value = true
  try {
    await api.delete(`/api/admin/puskesmas/${route.params.id}`)
    router.push('/dinas/puskesmas')
  } catch {
    deleting.value = false
  }
}

onMounted(load)
</script>

<template>
  <div class="space-y-6">
    <div>
      <RouterLink to="/dinas/puskesmas" class="text-sm text-blue-600 hover:underline">
        &larr; Kembali ke daftar puskesmas
      </RouterLink>
    </div>

    <div v-if="loading" class="text-sm text-gray-400">Memuat...</div>

    <template v-else>
      <div class="flex items-start justify-between flex-wrap gap-2">
        <div>
          <h1 class="text-xl font-semibold text-gray-900">{{ puskesmas.nama }}</h1>
          <p class="text-sm text-gray-500">{{ puskesmas.alamat }}</p>
          <p class="text-sm text-gray-500 mt-1">Username: <span class="font-mono">{{ puskesmas.user?.username }}</span></p>
        </div>
        <div class="flex gap-2">
          <RouterLink
            :to="`/dinas/puskesmas/${puskesmas.id}/edit`"
            class="text-sm border border-gray-300 rounded-md px-3 py-1.5 text-gray-700 hover:bg-gray-50"
          >
            Edit
          </RouterLink>
          <button
            :disabled="deleting"
            class="text-sm border border-red-300 rounded-md px-3 py-1.5 text-red-600 hover:bg-red-50 disabled:opacity-50"
            @click="hapus"
          >
            Hapus
          </button>
        </div>
      </div>

      <div class="grid md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg border border-gray-200">
          <h2 class="px-4 py-3 border-b border-gray-200 font-medium text-sm text-gray-900">Riwayat Kuesioner</h2>
          <p v-if="kuesionerHistory.length === 0" class="px-4 py-3 text-sm text-gray-400">Belum ada pengisian.</p>
          <ul v-else class="divide-y divide-gray-100">
            <li v-for="s in kuesionerHistory" :key="s.id">
              <RouterLink
                :to="`/dinas/puskesmas/${puskesmas.id}/kuesioner/${periodeKey(s.periode_bulan, s.periode_tahun)}`"
                class="flex items-center justify-between px-4 py-3 hover:bg-gray-50"
              >
                <span class="text-sm text-gray-900">{{ formatPeriode(s.periode_bulan, s.periode_tahun) }}</span>
                <span class="text-xs text-gray-400">{{ s.answers_count }}/21 terisi</span>
              </RouterLink>
            </li>
          </ul>
        </div>

        <div class="bg-white rounded-lg border border-gray-200">
          <h2 class="px-4 py-3 border-b border-gray-200 font-medium text-sm text-gray-900">Riwayat Observasi</h2>
          <p v-if="observasiHistory.length === 0" class="px-4 py-3 text-sm text-gray-400">Belum ada pengisian.</p>
          <ul v-else class="divide-y divide-gray-100">
            <li v-for="s in observasiHistory" :key="s.id">
              <RouterLink
                :to="`/dinas/puskesmas/${puskesmas.id}/observasi/${periodeKey(s.periode_bulan, s.periode_tahun)}`"
                class="flex items-center justify-between px-4 py-3 hover:bg-gray-50"
              >
                <span class="text-sm text-gray-900">{{ formatPeriode(s.periode_bulan, s.periode_tahun) }}</span>
                <span class="text-xs text-gray-400">{{ s.answers_count }}/96 terisi</span>
              </RouterLink>
            </li>
          </ul>
        </div>
      </div>

      <div class="bg-white rounded-lg border border-gray-200 p-4 max-w-md">
        <h2 class="font-medium text-sm text-gray-900 mb-2">Reset Password</h2>
        <div class="flex gap-2">
          <input
            v-model="newPassword"
            type="text"
            placeholder="Password baru (min. 8 karakter)"
            class="flex-1 text-sm border border-gray-300 rounded-md px-3 py-2"
          />
          <button
            :disabled="resetting"
            class="bg-gray-800 hover:bg-gray-900 disabled:opacity-50 text-white text-sm font-medium px-4 py-2 rounded-md"
            @click="resetPassword"
          >
            {{ resetting ? '...' : 'Reset' }}
          </button>
        </div>
        <p v-if="resetMessage" class="text-xs text-green-600 mt-2">{{ resetMessage }}</p>
        <p v-if="resetError" class="text-xs text-red-600 mt-2">{{ resetError }}</p>
      </div>
    </template>
  </div>
</template>

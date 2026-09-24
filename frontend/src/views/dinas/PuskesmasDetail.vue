<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import Swal from 'sweetalert2'
import api from '../../lib/api'
import { formatPeriode, periodeKey } from '../../lib/periode'
import { extractErrorMessage } from '../../lib/errors'
import Modal from '../../components/Modal.vue'
import CredentialBox from '../../components/CredentialBox.vue'

const route = useRoute()
const router = useRouter()
const loading = ref(true)
const puskesmas = ref<any>(null)
const kuesionerHistory = ref<any[]>([])
const observasiHistory = ref<any[]>([])

const showPasswordModal = ref(false)
const newPassword = ref('')
const resetting = ref(false)
const resetError = ref('')
const justResetPassword = ref<string | null>(null)
const deleting = ref(false)

async function load() {
  const { data } = await api.get(`/api/dashboard/puskesmas/${route.params.id}`)
  puskesmas.value = data.puskesmas
  kuesionerHistory.value = data.kuesioner_history
  observasiHistory.value = data.observasi_history
  loading.value = false
}

function openPasswordModal() {
  newPassword.value = ''
  resetError.value = ''
  justResetPassword.value = null
  showPasswordModal.value = true
}

async function resetPassword() {
  resetError.value = ''
  if (newPassword.value.length < 8) {
    resetError.value = 'Password minimal 8 karakter.'
    return
  }
  resetting.value = true
  try {
    await api.post(`/api/admin/puskesmas/${route.params.id}/reset-password`, { password: newPassword.value })
    justResetPassword.value = newPassword.value
  } catch (e) {
    resetError.value = extractErrorMessage(e)
  } finally {
    resetting.value = false
  }
}

async function hapus() {
  const result = await Swal.fire({
    title: 'Hapus Puskesmas Ini?',
    html: `Seluruh data pengisian &amp; akun login <strong>${puskesmas.value.nama}</strong> akan ikut terhapus permanen.<br>Tindakan ini tidak bisa dibatalkan.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal',
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280',
    reverseButtons: true,
  })

  if (!result.isConfirmed) return

  deleting.value = true
  try {
    await api.delete(`/api/admin/puskesmas/${route.params.id}`)
    router.push('/dinas/puskesmas')
  } catch (e) {
    deleting.value = false
    Swal.fire({
      title: 'Gagal Menghapus',
      text: extractErrorMessage(e),
      icon: 'error',
      confirmButtonColor: '#2563eb',
    })
  }
}

onMounted(load)
</script>

<template>
  <div class="space-y-6">
    <div>
      <RouterLink to="/dinas/puskesmas" class="inline-flex items-center gap-1 text-sm text-brand-700 hover:underline">
        &larr; Kembali ke daftar puskesmas
      </RouterLink>
    </div>

    <div v-if="loading" class="text-sm text-gray-400">Memuat...</div>

    <template v-else>
      <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <div class="flex items-start justify-between flex-wrap gap-3">
          <div>
            <h1 class="text-xl font-bold text-gray-900">{{ puskesmas.nama }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ puskesmas.alamat }}</p>
            <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-1 mt-3 text-sm">
              <div class="flex gap-1">
                <dt class="text-gray-400">Kepala Puskesmas:</dt>
                <dd class="text-gray-700">{{ puskesmas.kepala_puskesmas || '-' }}</dd>
              </div>
              <div class="flex gap-1">
                <dt class="text-gray-400">Kode Puskesmas:</dt>
                <dd class="text-gray-700 font-mono">{{ puskesmas.kode_puskesmas || '-' }}</dd>
              </div>
              <div class="flex gap-1">
                <dt class="text-gray-400">No. HP:</dt>
                <dd class="text-gray-700">{{ puskesmas.no_hp || '-' }}</dd>
              </div>
              <div class="flex gap-1">
                <dt class="text-gray-400">Email:</dt>
                <dd class="text-gray-700">{{ puskesmas.email || '-' }}</dd>
              </div>
            </dl>
          </div>
          <div class="flex flex-wrap gap-2">
            <RouterLink
              :to="`/dinas/puskesmas/${puskesmas.id}/edit`"
              class="inline-flex items-center gap-1.5 text-sm font-medium border border-gray-300 rounded-lg px-3.5 py-2 text-gray-700 hover:bg-gray-50 transition"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 19.5H4.5" />
              </svg>
              Ubah Data
            </RouterLink>
            <button
              class="inline-flex items-center gap-1.5 text-sm font-medium border border-gray-300 rounded-lg px-3.5 py-2 text-gray-700 hover:bg-gray-50 transition"
              @click="openPasswordModal"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
              </svg>
              Ubah Password
            </button>
            <button
              :disabled="deleting"
              class="inline-flex items-center gap-1.5 text-sm font-medium border border-red-300 rounded-lg px-3.5 py-2 text-red-600 hover:bg-red-50 disabled:opacity-50 transition"
              @click="hapus"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
              </svg>
              Hapus Puskesmas Ini
            </button>
          </div>
        </div>

        <CredentialBox class="max-w-sm" :username="puskesmas.user?.username" />
      </div>

      <div class="grid md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
          <h2 class="px-4 py-3 border-b border-gray-200 bg-brand-50 font-semibold text-sm text-brand-800">Riwayat Kuesioner</h2>
          <p v-if="kuesionerHistory.length === 0" class="px-4 py-3 text-sm text-gray-400">Belum ada pengisian.</p>
          <ul v-else class="divide-y divide-gray-100">
            <li v-for="s in kuesionerHistory" :key="s.id">
              <RouterLink
                :to="`/dinas/puskesmas/${puskesmas.id}/kuesioner/${periodeKey(s.periode_bulan, s.periode_tahun)}`"
                class="flex items-center justify-between px-4 py-3 hover:bg-brand-50/60 transition"
              >
                <span class="text-[15px] text-gray-900">{{ formatPeriode(s.periode_bulan, s.periode_tahun) }}</span>
                <span class="text-sm text-gray-400">{{ s.answers_count }}/21 terisi</span>
              </RouterLink>
            </li>
          </ul>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
          <h2 class="px-4 py-3 border-b border-gray-200 bg-brand-50 font-semibold text-sm text-brand-800">Riwayat Observasi</h2>
          <p v-if="observasiHistory.length === 0" class="px-4 py-3 text-sm text-gray-400">Belum ada pengisian.</p>
          <ul v-else class="divide-y divide-gray-100">
            <li v-for="s in observasiHistory" :key="s.id">
              <RouterLink
                :to="`/dinas/puskesmas/${puskesmas.id}/observasi/${periodeKey(s.periode_bulan, s.periode_tahun)}`"
                class="flex items-center justify-between px-4 py-3 hover:bg-brand-50/60 transition"
              >
                <span class="text-[15px] text-gray-900">{{ formatPeriode(s.periode_bulan, s.periode_tahun) }}</span>
                <span class="text-sm text-gray-400">{{ s.answers_count }}/96 terisi</span>
              </RouterLink>
            </li>
          </ul>
        </div>
      </div>

      <Modal v-if="showPasswordModal" title="Ubah Password" @close="showPasswordModal = false">
        <div v-if="justResetPassword" class="space-y-3">
          <p class="text-sm text-gray-600">Password berhasil diganti. Sampaikan ke petugas puskesmas:</p>
          <CredentialBox :username="puskesmas.user?.username" :password="justResetPassword" />
          <button
            class="w-full text-sm text-gray-500 px-4 py-2"
            @click="showPasswordModal = false"
          >
            Selesai
          </button>
        </div>

        <div v-else class="space-y-3">
          <p class="text-sm text-gray-500">
            Password baru untuk akun <strong>{{ puskesmas.user?.username }}</strong>.
          </p>
          <input
            v-model="newPassword"
            type="text"
            placeholder="Password baru (min. 8 karakter)"
            class="w-full text-sm border border-gray-300 rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500"
            @keyup.enter="resetPassword"
          />
          <p v-if="resetError" class="text-xs text-red-600">{{ resetError }}</p>
          <div class="flex gap-2 pt-1">
            <button
              :disabled="resetting"
              class="flex-1 bg-brand-600 hover:bg-brand-700 disabled:opacity-50 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition"
              @click="resetPassword"
            >
              {{ resetting ? 'Menyimpan...' : 'Simpan Password' }}
            </button>
            <button
              class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2.5"
              @click="showPasswordModal = false"
            >
              Batal
            </button>
          </div>
        </div>
      </Modal>
    </template>
  </div>
</template>

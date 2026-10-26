<script setup lang="ts">
import { ref } from 'vue'
import Swal from 'sweetalert2'
import api from '../lib/api'
import { extractErrorMessage } from '../lib/errors'
import { formatFileSize, type Attachment, type ReusableAttachment } from '../lib/attachments'
import { formatPeriode } from '../lib/periode'

const props = defineProps<{
  kind: 'questionnaire' | 'observation'
  itemId: number
  attachments: Attachment[]
  readonly?: boolean
}>()

const emit = defineEmits<{
  attached: [itemId: number, attachments: Attachment[]]
  deleted: [itemId: number, attachmentId: number]
}>()

const uploading = ref(false)
const loadingReusable = ref(false)
const reusingAttachmentId = ref<number | null>(null)
const showReusable = ref(false)
const reusableAttachments = ref<ReusableAttachment[] | null>(null)
const error = ref('')

function isImage(mime: string) {
  return mime.startsWith('image/')
}

async function onFilesPicked(e: Event) {
  const input = e.target as HTMLInputElement
  const files = input.files
  if (!files || !files.length) return

  const form = new FormData()
  Array.from(files).forEach((f) => form.append('files[]', f))

  uploading.value = true
  error.value = ''
  try {
    const { data } = await api.post(`/api/${props.kind}/items/${props.itemId}/attachments`, form)
    emit('attached', props.itemId, data)
  } catch (err) {
    error.value = extractErrorMessage(err)
  } finally {
    uploading.value = false
    input.value = ''
  }
}

async function toggleReusable() {
  showReusable.value = !showReusable.value
  if (!showReusable.value || reusableAttachments.value !== null) return

  loadingReusable.value = true
  error.value = ''
  try {
    const { data } = await api.get(`/api/${props.kind}/items/${props.itemId}/attachments/reusable`)
    reusableAttachments.value = data
  } catch (err) {
    error.value = extractErrorMessage(err)
  } finally {
    loadingReusable.value = false
  }
}

async function reuse(attachment: ReusableAttachment) {
  const period = formatPeriode(attachment.periode_bulan, attachment.periode_tahun)
  const confirmation = await Swal.fire({
    title: props.kind === 'observation' ? 'Tambahkan sebagai referensi?' : 'Gunakan bukti periode sebelumnya?',
    text: props.kind === 'observation'
      ? `Berkas dari periode ${period} hanya akan menjadi referensi. Berkas ini tidak membuktikan kondisi sarana pada periode sekarang.`
      : `Pastikan berkas dari periode ${period} masih berlaku untuk pertanyaan ini.`,
    icon: props.kind === 'observation' ? 'warning' : 'question',
    showCancelButton: true,
    confirmButtonText: props.kind === 'observation' ? 'Ya, tambahkan referensi' : 'Ya, gunakan bukti',
    cancelButtonText: 'Batal',
    confirmButtonColor: '#1f8143',
    cancelButtonColor: '#6b7280',
  })
  if (!confirmation.isConfirmed) return

  reusingAttachmentId.value = attachment.id
  error.value = ''
  try {
    const { data } = await api.post(`/api/${props.kind}/items/${props.itemId}/attachments/reuse`, {
      attachment_id: attachment.id,
      confirmed: true,
    })
    emit('attached', props.itemId, [data])
    reusableAttachments.value = reusableAttachments.value?.filter((item) => item.id !== attachment.id) ?? []
  } catch (err) {
    error.value = extractErrorMessage(err)
  } finally {
    reusingAttachmentId.value = null
  }
}

async function remove(attachment: Attachment) {
  if (!confirm(`Hapus lampiran "${attachment.original_name}"?`)) return
  error.value = ''
  try {
    await api.delete(`/api/${props.kind}/attachments/${attachment.id}`)
    emit('deleted', props.itemId, attachment.id)
  } catch (err) {
    error.value = (err as any)?.response?.data?.message ?? 'Gagal menghapus lampiran.'
  }
}
</script>

<template>
  <div class="mt-2">
    <div v-if="attachments.length" class="flex flex-wrap gap-1.5 mb-1.5">
      <div
        v-for="a in attachments"
        :key="a.id"
        class="inline-flex flex-wrap items-center gap-1 bg-gray-100 border border-gray-200 rounded-md pl-1.5 pr-1 py-1 text-xs text-gray-700 max-w-full"
      >
        <svg v-if="isImage(a.mime_type)" xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 4.5h18A1.5 1.5 0 0122.5 6v12a1.5 1.5 0 01-1.5 1.5H3A1.5 1.5 0 011.5 18V6A1.5 1.5 0 013 4.5zm13.5 6a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
        </svg>
        <a
          :href="`/api/${kind}/attachments/${a.id}/download`"
          target="_blank"
          class="truncate hover:underline hover:text-brand-700"
          :title="a.original_name"
        >
          {{ a.original_name }}
        </a>
        <span class="text-gray-400 shrink-0">({{ formatFileSize(a.size) }})</span>
        <span
          v-if="a.reused_from_attachment_id && kind === 'questionnaire'"
          class="text-amber-800 bg-amber-100 rounded px-1.5 py-0.5"
        >
          Digunakan dari {{ a.reused_from_periode_bulan && a.reused_from_periode_tahun
            ? formatPeriode(a.reused_from_periode_bulan, a.reused_from_periode_tahun)
            : 'periode sebelumnya' }}
        </span>
        <span
          v-else-if="a.reused_from_attachment_id && kind === 'observation'"
          class="text-amber-900 bg-amber-100 rounded px-1.5 py-0.5"
        >
          Referensi {{ a.reused_from_periode_bulan && a.reused_from_periode_tahun
            ? formatPeriode(a.reused_from_periode_bulan, a.reused_from_periode_tahun)
            : 'periode lalu' }}; bukan bukti kondisi saat ini
        </span>
        <button
          v-if="!readonly"
          type="button"
          class="shrink-0 text-gray-400 hover:text-red-600 ml-0.5"
          title="Hapus lampiran"
          @click="remove(a)"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <label
      v-if="!readonly"
      class="inline-flex items-center gap-1 text-xs font-medium text-brand-700 hover:text-brand-800 cursor-pointer"
      :class="{ 'opacity-50 pointer-events-none': uploading }"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
      </svg>
      {{ uploading ? 'Mengunggah...' : 'Tambah Lampiran/Bukti' }}
      <input type="file" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" class="hidden" :disabled="uploading" @change="onFilesPicked" />
    </label>
    <button
      v-if="!readonly"
      type="button"
      class="inline-flex items-center gap-1 text-xs font-medium text-brand-700 hover:text-brand-800 ml-3 disabled:opacity-50"
      :disabled="loadingReusable"
      @click="toggleReusable"
    >
      {{ showReusable ? 'Tutup bukti periode sebelumnya' : kind === 'observation' ? 'Pilih foto lama sebagai referensi' : 'Gunakan bukti dari periode sebelumnya' }}
    </button>
    <div v-if="showReusable" class="mt-2 rounded-lg border border-gray-200 bg-gray-50 p-3 space-y-2">
      <p v-if="kind === 'observation'" class="text-xs text-amber-900">
        Foto dari periode sebelumnya hanya menjadi referensi dan tidak membuktikan kondisi sarana pada periode sekarang.
      </p>
      <p v-else class="text-xs text-gray-600">
        Pilih bukti untuk pertanyaan yang sama. Pastikan bukti masih berlaku sebelum menggunakannya kembali.
      </p>
      <p v-if="loadingReusable" class="text-xs text-gray-500">Memuat bukti periode sebelumnya...</p>
      <p v-else-if="reusableAttachments?.length === 0" class="text-xs text-gray-500">
        Belum ada bukti dari periode sebelumnya untuk pertanyaan ini.
      </p>
      <div
        v-for="previous in reusableAttachments ?? []"
        :key="previous.id"
        class="flex flex-wrap items-center justify-between gap-2 rounded-md border border-gray-200 bg-white px-3 py-2"
      >
        <div class="min-w-0">
          <a
            :href="`/api/${kind}/attachments/${previous.id}/download`"
            target="_blank"
            class="block truncate text-xs font-medium text-brand-700 hover:underline"
            :title="previous.original_name"
          >
            {{ previous.original_name }}
          </a>
          <p class="text-[11px] text-gray-500">
            Periode {{ formatPeriode(previous.periode_bulan, previous.periode_tahun) }}
            · Diunggah {{ new Date(previous.created_at).toLocaleDateString('id-ID') }}
            · {{ formatFileSize(previous.size) }}
          </p>
        </div>
        <button
          type="button"
          class="shrink-0 rounded-md bg-brand-600 px-2.5 py-1.5 text-xs font-medium text-white hover:bg-brand-700 disabled:opacity-50"
          :disabled="reusingAttachmentId !== null"
          @click="reuse(previous)"
        >
          {{ reusingAttachmentId === previous.id ? 'Memproses...' : kind === 'observation' ? 'Tambahkan referensi' : 'Gunakan bukti ini' }}
        </button>
      </div>
    </div>
    <p v-if="error" class="text-xs text-red-600 mt-0.5">{{ error }}</p>
  </div>
</template>

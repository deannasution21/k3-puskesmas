<script setup lang="ts">
import { ref } from 'vue'
import api from '../lib/api'
import { formatFileSize, type Attachment } from '../lib/attachments'

const props = defineProps<{
  kind: 'questionnaire' | 'observation'
  itemId: number
  attachments: Attachment[]
  readonly?: boolean
}>()

const emit = defineEmits<{
  uploaded: [itemId: number, attachments: Attachment[]]
  deleted: [itemId: number, attachmentId: number]
}>()

const uploading = ref(false)
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
    emit('uploaded', props.itemId, data)
  } catch (err) {
    error.value = (err as any)?.response?.data?.message ?? 'Gagal mengunggah lampiran.'
  } finally {
    uploading.value = false
    input.value = ''
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
        class="inline-flex items-center gap-1 bg-gray-100 border border-gray-200 rounded-md pl-1.5 pr-1 py-1 text-xs text-gray-700 max-w-[220px]"
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
    <p v-if="error" class="text-xs text-red-600 mt-0.5">{{ error }}</p>
  </div>
</template>

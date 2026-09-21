<script setup lang="ts">
import { computed } from 'vue'

interface Item {
  id: number
  kategori_kode: string
  kategori: string
  nomor: number
  pertanyaan: string
}

const props = defineProps<{
  items: Item[]
  modelValue: Record<number, 'ya' | 'tidak' | undefined>
  readonly?: boolean
}>()

const emit = defineEmits<{
  'update:modelValue': [value: Record<number, 'ya' | 'tidak' | undefined>]
}>()

const grouped = computed(() => {
  const map = new Map<string, { kategori: string; items: Item[] }>()
  for (const item of props.items) {
    if (!map.has(item.kategori_kode)) {
      map.set(item.kategori_kode, { kategori: item.kategori, items: [] })
    }
    map.get(item.kategori_kode)!.items.push(item)
  }
  return [...map.entries()].sort(([a], [b]) => a.localeCompare(b))
})

function setJawaban(itemId: number, jawaban: 'ya' | 'tidak') {
  if (props.readonly) return
  emit('update:modelValue', { ...props.modelValue, [itemId]: jawaban })
}
</script>

<template>
  <div class="space-y-6">
    <section v-for="[kode, group] in grouped" :key="kode" class="bg-white rounded-lg border border-gray-200">
      <h2 class="px-4 py-3 border-b border-gray-200 font-medium text-sm text-gray-900">
        {{ kode }}. {{ group.kategori }}
      </h2>
      <ul class="divide-y divide-gray-100">
        <li v-for="item in group.items" :key="item.id" class="px-4 py-3 flex items-start justify-between gap-4">
          <p class="text-sm text-gray-700 flex-1">{{ item.nomor }}. {{ item.pertanyaan }}</p>
          <div class="flex gap-2 shrink-0">
            <button
              type="button"
              :disabled="readonly"
              class="px-3 py-1 text-xs rounded-md border"
              :class="modelValue[item.id] === 'ya'
                ? 'bg-green-600 text-white border-green-600'
                : 'border-gray-300 text-gray-600 hover:bg-gray-50'"
              @click="setJawaban(item.id, 'ya')"
            >
              Ya
            </button>
            <button
              type="button"
              :disabled="readonly"
              class="px-3 py-1 text-xs rounded-md border"
              :class="modelValue[item.id] === 'tidak'
                ? 'bg-red-600 text-white border-red-600'
                : 'border-gray-300 text-gray-600 hover:bg-gray-50'"
              @click="setJawaban(item.id, 'tidak')"
            >
              Tidak
            </button>
          </div>
        </li>
      </ul>
    </section>
  </div>
</template>

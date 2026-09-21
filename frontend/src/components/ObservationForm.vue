<script setup lang="ts">
import { computed } from 'vue'

interface Item {
  id: number
  kategori_kode: string
  kategori: string
  nomor: number
  item_teks: string
  skala_kondisi: 'baik_buruk' | 'baik_rusak_ringan_rusak_berat'
}

export interface ObservationAnswer {
  ada?: 'ada' | 'tidak_ada'
  kondisi?: string | null
  keterangan?: string | null
}

const props = defineProps<{
  items: Item[]
  modelValue: Record<number, ObservationAnswer>
  readonly?: boolean
}>()

const emit = defineEmits<{
  'update:modelValue': [value: Record<number, ObservationAnswer>]
}>()

const kondisiOptions: Record<Item['skala_kondisi'], { value: string; label: string }[]> = {
  baik_buruk: [
    { value: 'baik', label: 'Baik' },
    { value: 'buruk', label: 'Buruk' },
  ],
  baik_rusak_ringan_rusak_berat: [
    { value: 'baik', label: 'Baik' },
    { value: 'rusak_ringan', label: 'Rusak Ringan' },
    { value: 'rusak_berat', label: 'Rusak Berat' },
  ],
}

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

function patch(itemId: number, patch: ObservationAnswer) {
  if (props.readonly) return
  const current = props.modelValue[itemId] ?? {}
  emit('update:modelValue', { ...props.modelValue, [itemId]: { ...current, ...patch } })
}

function setAda(item: Item, ada: 'ada' | 'tidak_ada') {
  patch(item.id, { ada })
}
</script>

<template>
  <div class="space-y-6">
    <section v-for="[kode, group] in grouped" :key="kode" class="bg-white rounded-lg border border-gray-200">
      <h2 class="px-4 py-3 border-b border-gray-200 font-medium text-sm text-gray-900">
        {{ kode }}. {{ group.kategori }}
      </h2>
      <ul class="divide-y divide-gray-100">
        <li v-for="item in group.items" :key="item.id" class="px-4 py-3 space-y-2">
          <p class="text-sm text-gray-700">{{ item.nomor }}. {{ item.item_teks }}</p>

          <div class="flex flex-wrap items-center gap-2">
            <button
              type="button"
              :disabled="readonly"
              class="px-3 py-1 text-xs rounded-md border"
              :class="modelValue[item.id]?.ada === 'ada'
                ? 'bg-green-600 text-white border-green-600'
                : 'border-gray-300 text-gray-600 hover:bg-gray-50'"
              @click="setAda(item, 'ada')"
            >
              Ada
            </button>
            <button
              type="button"
              :disabled="readonly"
              class="px-3 py-1 text-xs rounded-md border"
              :class="modelValue[item.id]?.ada === 'tidak_ada'
                ? 'bg-red-600 text-white border-red-600'
                : 'border-gray-300 text-gray-600 hover:bg-gray-50'"
              @click="setAda(item, 'tidak_ada')"
            >
              Tidak Ada
            </button>

            <select
              v-if="modelValue[item.id]?.ada === 'ada'"
              class="ml-2 text-xs border border-gray-300 rounded-md px-2 py-1 disabled:bg-gray-100"
              :disabled="readonly"
              :value="modelValue[item.id]?.kondisi ?? ''"
              @change="patch(item.id, { kondisi: ($event.target as HTMLSelectElement).value || null })"
            >
              <option value="" disabled>Kondisi...</option>
              <option v-for="opt in kondisiOptions[item.skala_kondisi]" :key="opt.value" :value="opt.value">
                {{ opt.label }}
              </option>
            </select>

            <input
              type="text"
              placeholder="Keterangan/temuan (opsional)"
              class="flex-1 min-w-[160px] text-xs border border-gray-300 rounded-md px-2 py-1 disabled:bg-gray-100"
              :disabled="readonly"
              :value="modelValue[item.id]?.keterangan ?? ''"
              @input="patch(item.id, { keterangan: ($event.target as HTMLInputElement).value })"
            />
          </div>
        </li>
      </ul>
    </section>
  </div>
</template>

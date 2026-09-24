<script setup lang="ts">
defineProps<{
  title: string
}>()

const emit = defineEmits<{
  close: []
}>()
</script>

<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/40" @click="emit('close')" />

      <div class="relative bg-white rounded-xl shadow-lg w-full max-w-sm">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
          <h2 class="font-semibold text-gray-900">{{ title }}</h2>
          <button class="text-gray-400 hover:text-gray-600" @click="emit('close')">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="p-5">
          <slot />
        </div>
      </div>
    </div>
  </Teleport>
</template>

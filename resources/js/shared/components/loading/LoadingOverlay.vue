<script setup lang="ts">
import { Icon } from '@iconify/vue'

defineProps<{
    title?: string
    subtitle?: string
}>()
</script>

<template>
    <div
        class="inset-0 gap-3 bg-surface-0/80 backdrop-blur-sm absolute z-[9999] flex flex-col items-center justify-center"
    >
        <Icon icon="eos-icons:three-dots-loading" class="h-24 w-24 text-primary-500" />
        <div v-if="title || subtitle" class="gap-1 flex flex-col items-center text-center">
            <p v-if="title" class="text-base font-semibold text-surface-800">{{ title }}</p>
            <p v-if="subtitle" class="text-sm text-surface-500">{{ subtitle }}</p>
        </div>
    </div>
</template>

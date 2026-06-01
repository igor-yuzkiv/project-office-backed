<script setup lang="ts">
import { useClipboard } from '@vueuse/core'
import { Icon } from '@iconify/vue'

defineProps<{
    text: string | null | undefined
}>()

const { copy, copied } = useClipboard()
</script>

<template>
    <span v-if="text" class="gap-2 inline-flex items-center">
        <button
            type="button"
            class="text-surface-400 hover:text-surface-700 dark:hover:text-surface-200 transition-colors"
            :title="copied ? 'Copied!' : 'Copy'"
            @click="copy(text)"
        >
            <Icon :icon="copied ? 'mdi:check' : 'mdi:content-copy'" class="h-3.5 w-3.5" />
        </button>

        {{ text }}
    </span>
    <span v-else class="text-surface-400">—</span>
</template>

<script setup lang="ts">
import { useClipboard } from '@vueuse/core'
import { Icon } from '@iconify/vue'

defineProps<{
    text: string | null | undefined
    hideCopyIcon?: boolean
    iconClass?: string
}>()

const { copy, copied } = useClipboard()
</script>

<template>
    <div v-if="text" class="gap-2 inline-flex cursor-pointer items-center" @click.stop="copy(text)">
        <Icon
            v-if="!hideCopyIcon"
            :class="[
                'text-surface-400 hover:text-surface-700 dark:hover:text-surface-200 transition-colors',
                iconClass,
            ]"
            :icon="copied ? 'mdi:check' : 'tabler:copy'"
        />

        <span>
            <slot>{{ text }}</slot>
        </span>
    </div>
</template>

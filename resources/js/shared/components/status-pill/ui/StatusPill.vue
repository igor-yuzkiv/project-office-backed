<script setup lang="ts">
import Tag from 'primevue/tag'
import { Icon } from '@iconify/vue'
import type { StatusColors } from '@/shared/types'

withDefaults(
    defineProps<{
        label: string
        colors: StatusColors
        dot?: boolean
        icon?: string
    }>(),
    { dot: true }
)
</script>

<template>
    <Tag
        :style="{
            backgroundColor: colors.bg,
            color: colors.fg,
            border: `1px solid color-mix(in srgb, ${colors.fg} 45%, transparent)`,
        }"
        :pt="{
            root: { class: '!gap-1.5 !rounded-[4px] !px-2 !py-0 !text-[12px] !font-semibold whitespace-nowrap' },
        }"
    >
        <Icon v-if="icon" :icon="icon" />
        <span v-else-if="dot" class="size-[7px] shrink-0 rounded-full bg-current" data-role="dot" />
        {{ label }}
    </Tag>
</template>

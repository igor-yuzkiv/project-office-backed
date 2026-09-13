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
        :style="{ backgroundColor: colors.bg, color: colors.fg }"
        :pt="{
            root: { class: '!gap-1.5 !rounded-full !px-2 !py-0.5 !text-[12.5px] !font-medium whitespace-nowrap' },
        }"
    >
        <Icon v-if="icon" :icon="icon" />
        <span v-else-if="dot" class="size-[7px] shrink-0 rounded-full bg-current" data-role="dot" />
        {{ label }}
    </Tag>
</template>

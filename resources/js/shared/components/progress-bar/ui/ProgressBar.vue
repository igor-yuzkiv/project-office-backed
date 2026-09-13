<script setup lang="ts">
import { computed } from 'vue'
import type { StatusColors } from '@/shared/types'

export type ProgressSegment = { value: number; colors: StatusColors }

const props = defineProps<{
    segments: ProgressSegment[]
    total: number
}>()

const widths = computed(() =>
    props.segments
        .filter((segment) => segment.value > 0 && props.total > 0)
        .map((segment) => ({
            width: `${Math.min(100, (segment.value / props.total) * 100)}%`,
            backgroundColor: segment.colors.fg,
        }))
)
</script>

<template>
    <div class="gap-1 flex flex-col">
        <div class="bg-line h-1.5 flex overflow-hidden rounded-full" role="progressbar" :aria-valuemax="total">
            <span v-for="(style, index) in widths" :key="index" class="block h-full shrink-0" :style="style" />
        </div>
        <span v-if="$slots.default" class="text-ink-3 text-xs">
            <slot />
        </span>
    </div>
</template>

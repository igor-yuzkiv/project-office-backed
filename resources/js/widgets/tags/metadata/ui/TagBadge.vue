<script setup lang="ts">
import type { ITag } from '@/entities/tag/types'
import { Icon } from '@iconify/vue'

defineProps<{
    tag: ITag
    closable?: boolean
}>()
</script>

<template>
    <span
        class="bg-code-bg text-ink-2 gap-1.5 px-2 py-0.5 inline-flex shrink-0 items-center truncate rounded-[5px] text-[12.5px] whitespace-nowrap"
    >
        <Icon v-if="closable" icon="material-symbols:close" class="cursor-pointer" />
        <span class="truncate">{{ tag.name }}</span>
    </span>
</template>

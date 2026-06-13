<script setup lang="ts">
import { computed } from 'vue'
import Tag from 'primevue/tag'
import type { ITag } from '@/entities/tag/types'
import { getContrastColor } from '@/shared/utils/color.util.ts'
import { Icon } from '@iconify/vue'

const props = defineProps<{
    tag: ITag
    closable?: boolean
}>()

const styles = computed(() => ({
    backgroundColor: props.tag.color,
    color: getContrastColor(props.tag.color),
}))
</script>

<template>
    <Tag :style="styles" class="gap-2 px-1 py-0.5 flex shrink-0 truncate">
        <Icon v-if="closable" icon="material-symbols:close" class="cursor-pointer" />
        <span class="truncate">{{ tag.name }}</span>
    </Tag>
</template>

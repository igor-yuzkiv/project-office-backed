<script setup lang="ts">
import { computed } from 'vue'
import Tag from 'primevue/tag'
import type { ITag } from '@/entities/tag/types'
import { getContrastColor } from '@/shared/utils/color.util.ts'

const props = defineProps<{
    tag: ITag
}>()

const styles = computed(() => ({
    backgroundColor: props.tag.color,
    color: getContrastColor(props.tag.color),
}))
</script>

<template>
    <Tag :style="styles">{{ tag.name }}</Tag>
</template>

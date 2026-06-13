<script setup lang="ts">
import Dialog from 'primevue/dialog'
import type { ITag } from '@/entities/tag/types'
import TagBadge from './TagBadge.vue'

defineProps<{
    tags: ITag[]
}>()

const visible = defineModel<boolean>({ required: true })
</script>

<template>
    <Dialog v-model:visible="visible" header="Tags" modal :style="{ width: '24rem' }">
        <div v-if="tags.length > 0" class="gap-2 flex flex-wrap">
            <TagBadge v-for="tag in tags" :key="tag.id" :tag="tag" />
        </div>
        <p v-else class="text-surface-400 text-sm">No tags yet</p>
    </Dialog>
</template>

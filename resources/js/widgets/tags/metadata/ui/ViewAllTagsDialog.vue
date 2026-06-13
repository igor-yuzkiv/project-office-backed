<script setup lang="ts">
import Dialog from 'primevue/dialog'
import type { ITag } from '@/entities/tag/types'
import TagBadge from './TagBadge.vue'

defineProps<{ tags: ITag[] }>()

const visible = defineModel<boolean>({ required: true })
</script>

<template>
    <Dialog v-model:visible="visible" modal :closable="true" :style="{ width: '24rem' }">
        <template #header>
            <div class="flex flex-col">
                <div class="gap-2 flex items-center">
                    <i class="pi pi-tag text-primary" />
                    <span class="text-base font-semibold">Tags</span>
                </div>
                <slot name="subtitle"> </slot>
            </div>
        </template>

        <div v-if="tags.length > 0" class="gap-2 py-1 flex flex-wrap">
            <TagBadge v-for="tag in tags" :key="tag.id" :tag="tag" />
        </div>
        <p v-else class="text-surface-400 text-sm py-1">No tags yet</p>
    </Dialog>
</template>

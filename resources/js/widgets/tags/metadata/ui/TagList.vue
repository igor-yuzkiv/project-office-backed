<script setup lang="ts">
import { computed, ref } from 'vue'
import type { ITag } from '@/entities/tag/types'
import TagBadge from './TagBadge.vue'
import ViewAllTagsDialog from './ViewAllTagsDialog.vue'

const props = withDefaults(
    defineProps<{
        tags: ITag[]
        visibleLimit?: number
    }>(),
    {
        tags: () => [],
        visibleLimit: 6,
    }
)

const showViewDialog = ref(false)

const visibleTags = computed(() => props.tags.slice(0, props.visibleLimit))
const hiddenCount = computed(() => props.tags.length - props.visibleLimit)
const hasMore = computed(() => hiddenCount.value > 0)
</script>

<template>
    <div v-if="tags.length > 0" class="gap-1.5 flex flex-wrap items-center">
        <TagBadge v-for="tag in visibleTags" :key="tag.id" :tag="tag" />
        <span
            v-if="hasMore"
            class="cursor-pointer text-sm text-surface-400 hover:text-surface-600"
            @click="showViewDialog = true"
        >
            +{{ hiddenCount }}
        </span>

        <ViewAllTagsDialog v-if="showViewDialog" v-model="showViewDialog" :tags="tags" />
    </div>
</template>

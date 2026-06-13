<script setup lang="ts">
import { computed, ref } from 'vue'
import type { ITag } from '@/entities/tag/types'
import TagBadge from './TagBadge.vue'
import ViewAllTagsDialog from './ViewAllTagsDialog.vue'

const props = withDefaults(
    defineProps<{
        tags: ITag[]
        visibleLimit?: number
        inline?: boolean
    }>(),
    {
        tags: () => [],
        visibleLimit: 5,
    }
)

const showViewDialog = ref(false)

const visibleTags = computed(() => props.tags.slice(0, props.visibleLimit))
const hiddenCount = computed(() => props.tags.length - props.visibleLimit)
const hasMore = computed(() => hiddenCount.value > 0)
</script>

<template>
    <div v-if="tags.length > 0" class="gap-1.5 items-center" :class="inline ? 'inline-flex' : 'flex flex-wrap'">
        <TagBadge v-for="tag in visibleTags" :key="tag.id" :tag="tag" />
        <span
            v-if="hasMore"
            class="text-sm text-surface-400 hover:text-surface-600 dark:hover:text-surface-200 cursor-pointer"
            @click.stop="showViewDialog = true"
        >
            +{{ hiddenCount }}
        </span>

        <ViewAllTagsDialog v-if="showViewDialog" v-model="showViewDialog" :tags="tags" />
    </div>
</template>

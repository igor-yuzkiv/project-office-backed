<script setup lang="ts">
import { computed, ref } from 'vue'
import Button from 'primevue/button'
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
        visibleLimit: 5,
    }
)

const showViewAll = ref(false)

const visibleTags = computed(() => props.tags.slice(0, props.visibleLimit))
const hasMore = computed(() => props.tags.length > props.visibleLimit)
</script>

<template>
    <div v-if="tags.length > 0" class="gap-1.5 flex flex-wrap items-center">
        <TagBadge v-for="tag in visibleTags" :key="tag.id" :tag="tag" />
        <Button
            v-if="hasMore"
            :label="`+${tags.length - visibleLimit} more`"
            size="small"
            variant="text"
            severity="secondary"
            @click="showViewAll = true"
        />
        <ViewAllTagsDialog v-if="hasMore && showViewAll" v-model="showViewAll" :tags="tags" />
    </div>
</template>

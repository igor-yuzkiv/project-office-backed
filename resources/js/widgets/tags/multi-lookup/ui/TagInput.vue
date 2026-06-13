<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { refDebounced } from '@vueuse/core'
import MultiSelect from 'primevue/multiselect'
import Button from 'primevue/button'
import type { ITag } from '@/entities/tag/types'
import { useTagsSearchQuery } from '@/entities/tag/queries'
import { TagBadge } from '@/widgets/tags/metadata'
import { CreateTagDialog } from '@/widgets/tags/create-dialog'

const modelValue = defineModel<string[]>({ required: true })

const searchQuery = ref('')
const debouncedSearchQuery = refDebounced(searchQuery, 300)
const showCreateDialog = ref(false)

const { tags: searchResults } = useTagsSearchQuery(debouncedSearchQuery)

const tagCache = ref<Map<string, ITag>>(new Map())

watch(
    searchResults,
    (results) => {
        for (const tag of results) {
            tagCache.value.set(tag.id, tag)
        }
    },
    { immediate: true }
)

// Always include currently-selected tags in options so MultiSelect can render their checkboxes
const options = computed<ITag[]>(() => {
    const searchIds = new Set(searchResults.value.map((t) => t.id))
    const selectedNotInSearch = modelValue.value
        .filter((id) => !searchIds.has(id))
        .map((id) => tagCache.value.get(id))
        .filter((t): t is ITag => t !== undefined)
    return [...selectedNotInSearch, ...searchResults.value]
})

const selectedTags = computed<ITag[]>(() =>
    modelValue.value.map((id) => tagCache.value.get(id)).filter((t): t is ITag => t !== undefined)
)

function onFilter(event: { value: string }) {
    searchQuery.value = event.value
}

function onTagCreated(tag: ITag) {
    tagCache.value.set(tag.id, tag)
    modelValue.value = [...modelValue.value, tag.id]
}
</script>

<template>
    <div class="gap-2 flex items-center">
        <MultiSelect
            v-model="modelValue"
            :options="options"
            option-label="name"
            option-value="id"
            filter
            :filter-fields="['name']"
            placeholder="Search tags..."
            class="flex-1"
            :show-toggle-all="false"
            @filter="onFilter"
        >
            <template #value>
                <div v-if="selectedTags.length > 0" class="gap-1 py-0.5 flex flex-wrap">
                    <TagBadge v-for="tag in selectedTags" :key="tag.id" :tag="tag" />
                </div>
                <span v-else class="text-surface-400">Search tags...</span>
            </template>
        </MultiSelect>

        <Button
            icon="pi pi-plus"
            severity="secondary"
            outlined
            aria-label="Create tag"
            @click="showCreateDialog = true"
        />

        <CreateTagDialog v-model:visible="showCreateDialog" @created="onTagCreated" />
    </div>
</template>

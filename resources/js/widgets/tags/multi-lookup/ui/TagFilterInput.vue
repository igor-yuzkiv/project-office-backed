<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { refDebounced } from '@vueuse/core'
import MultiSelect from 'primevue/multiselect'
import type { ITag } from '@/entities/tag/types'
import { useTagsSearchQuery } from '@/entities/tag/queries'

const modelValue = defineModel<string[] | null>({ default: null })

const searchQuery = ref('')
const debouncedSearchQuery = refDebounced(searchQuery, 300)

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
    const selectedNotInSearch = (modelValue.value ?? [])
        .filter((id) => !searchIds.has(id))
        .map((id) => tagCache.value.get(id))
        .filter((t): t is ITag => t !== undefined)
    return [...selectedNotInSearch, ...searchResults.value]
})

function onFilter(event: { value: string }) {
    searchQuery.value = event.value
}
</script>

<template>
    <MultiSelect
        v-model="modelValue"
        :options="options"
        option-label="name"
        option-value="id"
        filter
        placeholder="Search tags..."
        class="w-full"
        @filter="onFilter"
    />
</template>

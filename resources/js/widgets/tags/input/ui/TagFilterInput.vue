<script setup lang="ts">
import { ref } from 'vue'
import MultiSelect from 'primevue/multiselect'
import { useTagsSearchQuery } from '@/entities/tag/queries'

const modelValue = defineModel<string[] | null>({ default: null })
const searchQuery = ref('')

const { tags } = useTagsSearchQuery(searchQuery)

function onFilter(event: { value: string }) {
    searchQuery.value = event.value
}
</script>

<template>
    <MultiSelect
        :model-value="modelValue"
        :options="tags"
        option-label="name"
        option-value="id"
        filter
        placeholder="Search tags..."
        class="w-full"
        @update:model-value="modelValue = $event"
        @filter="onFilter"
    />
</template>

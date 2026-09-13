<script setup lang="ts">
import { ACTIVITY_SEGMENTS, type ActivitySegment } from '@/entities/audit-trail'
import { FilterButton, type useFilterSidebar } from '@/shared/filters'

defineProps<{
    filterSidebar: ReturnType<typeof useFilterSidebar>
}>()

const segment = defineModel<ActivitySegment>('segment', { required: true })
</script>

<template>
    <div class="gap-2 mb-2 flex flex-wrap items-center">
        <FilterButton v-bind="filterSidebar.buttonProps.value" />
        <span class="flex-1" />
        <div class="gap-0.5 text-ink-2 flex items-center text-[13px]" role="group" aria-label="Show">
            <button
                v-for="option in ACTIVITY_SEGMENTS"
                :key="option.value"
                type="button"
                class="px-2 py-0.5 hover:text-ink cursor-pointer rounded-[5px]"
                :class="{ 'bg-hover text-ink': segment === option.value }"
                :aria-pressed="segment === option.value"
                @click="segment = option.value"
            >
                {{ option.label }}
            </button>
        </div>
    </div>
</template>

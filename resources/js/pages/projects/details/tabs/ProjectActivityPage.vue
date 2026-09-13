<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'
import { ACTIVITY_TYPE_LABELS, type AuditRecordFilters, type KnownAuditRecordSubjectType } from '@/entities/audit-trail'
import { usePersistedListState } from '@/shared/composables'
import { createFilterDefMap, FilterButton, FilterSidebar, useFilterSidebar } from '@/shared/filters'
import { ActivityStream } from '@/widgets/audit-trail/activity-stream'

type ActivitySegment = 'all' | KnownAuditRecordSubjectType

const SEGMENTS: { value: ActivitySegment; label: string }[] = [
    { value: 'all', label: 'All' },
    { value: 'task', label: 'Tasks' },
    { value: 'task_list', label: 'Lists' },
    { value: 'project_document', label: 'Documents' },
]

const route = useRoute()
const projectId = route.params.id as string

// The project is the page's scope, so only the type is offered; the feed knows only "is any of".
const filterSidebar = useFilterSidebar(
    createFilterDefMap((map) =>
        map.addField('type', 'select', (d) =>
            d
                .label('Type')
                .matchMode('in')
                .withoutMatchMode()
                .setInputProps({
                    options: Object.entries(ACTIVITY_TYPE_LABELS).map(([value, label]) => ({ value, label })),
                    optionLabel: 'label',
                    optionValue: 'value',
                    placeholder: 'Select types',
                    filter: true,
                })
        )
    )
)

const segment = ref<ActivitySegment>('all')

usePersistedListState(
    { filters: filterSidebar.filtersSnapshot, segment },
    {
        key: 'project-activity',
        validate: (data) => SEGMENTS.some((s) => s.value === data.segment),
    }
)

const filters = computed<AuditRecordFilters>(() => {
    const types = filterSidebar.resolvedFilters.value.find((f) => f.field_name === 'type')?.value

    return {
        project_id: [projectId],
        type: Array.isArray(types) ? (types as string[]) : undefined,
        subject_type: segment.value === 'all' ? undefined : [segment.value],
    }
})
</script>

<template>
    <div class="flex flex-1 flex-col">
        <div class="gap-2 mb-2 flex flex-wrap items-center">
            <FilterButton v-bind="filterSidebar.buttonProps.value" />
            <span class="flex-1" />
            <div class="gap-0.5 text-ink-2 flex items-center text-[13px]" role="group" aria-label="Show">
                <button
                    v-for="option in SEGMENTS"
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

        <ActivityStream :filters="filters" :show-project="false" />

        <FilterSidebar v-bind="filterSidebar.sidebarProps.value" />
    </div>
</template>

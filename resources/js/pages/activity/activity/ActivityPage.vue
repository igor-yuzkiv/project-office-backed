<script setup lang="ts">
import { computed, ref } from 'vue'
import { useBreadcrumbs } from '@/app/shell'
import {
    type ActivitySegment,
    type AuditRecordFilters,
    createActivityFiltersDefMap,
    isActivitySegment,
    resolveActivityFilters,
} from '@/entities/audit-trail'
import { usePersistedListState } from '@/shared/composables'
import { FilterSidebar, useFilterSidebar } from '@/shared/filters'
import { ActivityFilters } from '@/widgets/audit-trail/activity-filters'
import { ActivityStream } from '@/widgets/audit-trail/activity-stream'

useBreadcrumbs([{ label: 'Activity' }])

const filterSidebar = useFilterSidebar(createActivityFiltersDefMap())

const segment = ref<ActivitySegment>('all')

usePersistedListState(
    { filters: filterSidebar.filtersSnapshot, segment },
    {
        key: 'activity',
        validate: (data) => isActivitySegment(data.segment),
    }
)

const filters = computed<AuditRecordFilters>(() => {
    const resolved = filterSidebar.resolvedFilters.value
    const projectId = resolved.find((f) => f.field_name === 'project_id')?.value

    return {
        project_id: typeof projectId === 'string' ? [projectId] : undefined,
        ...resolveActivityFilters(resolved, segment.value),
    }
})
</script>

<template>
    <div class="min-h-0 flex-1 overflow-auto">
        <div class="page-container">
            <ActivityFilters v-model:segment="segment" :filter-sidebar="filterSidebar" />

            <ActivityStream :filters="filters" />
        </div>

        <FilterSidebar v-bind="filterSidebar.sidebarProps.value" />
    </div>
</template>

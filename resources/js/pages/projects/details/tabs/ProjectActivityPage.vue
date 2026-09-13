<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'
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

const route = useRoute()
const projectId = route.params.id as string

// The project is the page's scope, so only the type is offered.
const filterSidebar = useFilterSidebar(createActivityFiltersDefMap({ withProject: false }))

const segment = ref<ActivitySegment>('all')

usePersistedListState(
    { filters: filterSidebar.filtersSnapshot, segment },
    {
        key: `project-activity:${projectId}`,
        validate: (data) => isActivitySegment(data.segment),
    }
)

const filters = computed<AuditRecordFilters>(() => ({
    project_id: [projectId],
    ...resolveActivityFilters(filterSidebar.resolvedFilters.value, segment.value),
}))
</script>

<template>
    <div class="flex flex-1 flex-col">
        <ActivityFilters v-model:segment="segment" :filter-sidebar="filterSidebar" />

        <ActivityStream :filters="filters" :show-project="false" />

        <FilterSidebar v-bind="filterSidebar.sidebarProps.value" />
    </div>
</template>

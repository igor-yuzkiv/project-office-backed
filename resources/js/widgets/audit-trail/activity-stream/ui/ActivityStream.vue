<script setup lang="ts">
import { computed, ref, toRef } from 'vue'
import Button from 'primevue/button'
import { useAuditRecordFeed, type AuditRecordFilters } from '@/entities/audit-trail'
import { groupRecordsByDay } from '../lib'
import ActivityStreamItem from './ActivityStreamItem.vue'

const props = withDefaults(
    defineProps<{
        filters?: AuditRecordFilters
        /** Show only the first N rows and no way to load more — the Overview block. */
        limit?: number
        showProject?: boolean
    }>(),
    { filters: () => ({}), showProject: true }
)

const { records, total, hasMore, loadMore, isPending, isFetching, isError, refetch } = useAuditRecordFeed(
    toRef(props, 'filters'),
    { perPage: props.limit }
)

/** Rows open independently, so two descriptions can be compared side by side. */
const expandedIds = ref(new Set<string>())

const visibleRecords = computed(() => (props.limit === undefined ? records.value : records.value.slice(0, props.limit)))
const dayGroups = computed(() => groupRecordsByDay(visibleRecords.value))

// A filter change empties the list while the next page is in flight; that is loading, not empty.
const isLoading = computed(() => isPending.value || isFetching.value)

function toggle(id: string) {
    const next = new Set(expandedIds.value)

    if (!next.delete(id)) {
        next.add(id)
    }

    expandedIds.value = next
}
</script>

<template>
    <div>
        <template v-if="records.length > 0">
            <template v-for="group in dayGroups" :key="group.key">
                <h3 class="type-meta mt-6 mb-1 font-medium first:mt-2">{{ group.label }}</h3>

                <ActivityStreamItem
                    v-for="record in group.records"
                    :key="record.id"
                    :record="record"
                    :expanded="expandedIds.has(record.id)"
                    :show-project="showProject"
                    @toggle="toggle(record.id)"
                />
            </template>
        </template>

        <p v-else-if="isLoading" class="type-meta-3 py-2">Loading…</p>

        <p v-else-if="!isError" class="type-meta py-2">No activity yet</p>

        <!-- An error here is a failed page — the first or the next one — so it offers its own retry. -->
        <div v-if="isError" class="gap-3 py-2 flex items-center">
            <span class="type-meta">Could not load activity.</span>
            <Button label="Retry" size="small" severity="secondary" text class="!h-7" @click="refetch()" />
        </div>

        <div v-else-if="limit === undefined && records.length > 0" class="gap-2 pt-3 flex items-center justify-between">
            <span class="type-meta-3">Showing {{ records.length }} of {{ total }}</span>
            <Button
                v-if="hasMore"
                :label="isFetching ? 'Loading…' : 'Load more'"
                size="small"
                severity="secondary"
                outlined
                class="!h-7"
                :disabled="isFetching"
                @click="loadMore"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import Button from 'primevue/button'
import { useAuditRecordFeed } from '@/entities/audit-trail'
import { DataPanel, type DataPanelState } from '@/shared/components/data-panel'
import { groupRecordsByDay } from '../lib'
import ActivityStreamItem from './ActivityStreamItem.vue'

const { records, hasMore, loadMore, isPending, isFetching, isError, refetch } = useAuditRecordFeed()

/** Rows open independently, so two events can be compared side by side. */
const expandedIds = ref(new Set<string>())

const dayGroups = computed(() => groupRecordsByDay(records.value))

/** Once rows are on screen, a failed request is a failed next page and not a failed panel. */
const state = computed<DataPanelState>(() => {
    if (records.value.length > 0) return 'ready'
    if (isPending.value) return 'pending'
    if (isError.value) return 'error'

    return 'empty'
})

function toggle(id: string) {
    const next = new Set(expandedIds.value)

    if (!next.delete(id)) {
        next.add(id)
    }

    expandedIds.value = next
}
</script>

<template>
    <DataPanel
        title="Activity"
        subtitle="Latest events across your projects"
        :state="state"
        empty-message="No activity yet"
        error-message="Could not load activity."
        class="flex flex-col"
        @retry="refetch()"
    >
        <div class="min-h-0 flex-1 overflow-y-auto">
            <template v-for="group in dayGroups" :key="group.key">
                <h3
                    class="bg-surface-50 dark:bg-surface-800 border-surface-200 dark:border-surface-700 text-surface-400 px-4 py-2 font-semibold border-b text-[11px] tracking-[0.08em] uppercase"
                >
                    {{ group.label }}
                </h3>

                <ActivityStreamItem
                    v-for="record in group.records"
                    :key="record.id"
                    :record="record"
                    :expanded="expandedIds.has(record.id)"
                    @toggle="toggle(record.id)"
                />
            </template>
        </div>

        <!--
            The next-page strip lives in the ready state rather than in DataPanel's footer slot, whose
            border shows in every state. An error here is a failed next page, not the end of the feed,
            so it offers its own retry.
        -->
        <div
            v-if="isError"
            class="border-surface-200 dark:border-surface-700 gap-3 px-4 py-3 flex items-center justify-center border-t"
        >
            <span class="text-surface-500 text-sm">Could not load more activity.</span>
            <Button label="Try again" size="small" severity="secondary" text @click="refetch()" />
        </div>

        <button
            v-else-if="hasMore"
            type="button"
            class="border-surface-200 dark:border-surface-700 text-primary hover:bg-surface-50 dark:hover:bg-surface-800/60 py-2.5 text-sm font-semibold w-full cursor-pointer border-t text-center disabled:cursor-default disabled:opacity-60"
            :disabled="isFetching"
            @click="loadMore"
        >
            {{ isFetching ? 'Loading…' : 'Load more' }}
        </button>
    </DataPanel>
</template>

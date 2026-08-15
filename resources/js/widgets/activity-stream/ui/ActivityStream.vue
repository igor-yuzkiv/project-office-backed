<script setup lang="ts">
import { computed, ref } from 'vue'
import Button from 'primevue/button'
import Skeleton from 'primevue/skeleton'
import { useAuditRecordFeed } from '@/entities/audit-record'
import { groupRecordsByDay } from '../lib'
import ActivityStreamItem from './ActivityStreamItem.vue'

const { records, hasMore, loadMore, isPending, isFetching, isError, refetch } = useAuditRecordFeed()

/** One row open at a time: the feed stays scannable and the page does not jump around. */
const expandedId = ref<string | null>(null)

const dayGroups = computed(() => groupRecordsByDay(records.value))
const isEmpty = computed(() => !isPending.value && !isError.value && records.value.length === 0)

function toggle(id: string) {
    expandedId.value = expandedId.value === id ? null : id
}
</script>

<template>
    <section
        class="border-surface-200 dark:border-surface-700 bg-surface-0 dark:bg-surface-900 rounded-xl overflow-hidden border"
    >
        <header
            class="border-surface-200 dark:border-surface-700 gap-3 px-4 py-3.5 flex items-baseline justify-between border-b"
        >
            <h2 class="text-surface-900 dark:text-surface-0 text-base font-semibold">Activity</h2>
            <span class="text-surface-400 text-xs">Latest events across your projects</span>
        </header>

        <div v-if="isPending && records.length === 0" class="gap-3 p-4 flex flex-col">
            <Skeleton v-for="n in 3" :key="n" height="2.5rem" />
        </div>

        <div v-else-if="isError && records.length === 0" class="gap-3 p-8 flex flex-col items-center">
            <p class="text-surface-500 text-sm">Could not load activity.</p>
            <Button label="Try again" size="small" severity="secondary" @click="refetch()" />
        </div>

        <p v-else-if="isEmpty" class="text-surface-400 p-8 text-sm text-center">No activity yet</p>

        <template v-else>
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
                    :expanded="expandedId === record.id"
                    @toggle="toggle(record.id)"
                />
            </template>

            <!-- An error while loading a further page is not the end of the feed, so it offers a retry. -->
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
                @click="loadMore()"
            >
                {{ isFetching ? 'Loading…' : 'Load more' }}
            </button>
        </template>
    </section>
</template>

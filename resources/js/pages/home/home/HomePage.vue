<script setup lang="ts">
import { useDashboardQuery } from '@/entities/dashboard'
import { ActivityStream } from '@/widgets/audit-trail/activity-stream'
import { DashboardSummary } from '@/widgets/home-dashboard/summary'
import { RecentTasks, RecentTaskLists } from '@/widgets/home-dashboard/recent'

const { summary, recentTasks, recentTaskLists, isPending, isError, refetch } = useDashboardQuery()
</script>

<template>
    <div class="gap-4 p-4 flex flex-1 flex-col overflow-auto">
        <DashboardSummary :summary="summary" :is-pending="isPending" :is-error="isError" @retry="refetch()" />

        <div class="gap-4 xl:grid-cols-3 grid">
            <div class="xl:col-span-2 gap-4 flex flex-col">
                <RecentTasks :tasks="recentTasks" :is-pending="isPending" :is-error="isError" @retry="refetch()" />
                <RecentTaskLists
                    :task-lists="recentTaskLists"
                    :is-pending="isPending"
                    :is-error="isError"
                    @retry="refetch()"
                />
            </div>

            <div class="xl:relative">
                <ActivityStream class="xl:absolute xl:inset-0" />
            </div>
        </div>
    </div>
</template>

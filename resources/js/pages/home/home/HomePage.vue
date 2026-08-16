<script setup lang="ts">
import { useDashboardQuery } from '@/features/dashboard'
import { ActivityStream } from '@/widgets/activity-stream'
import { DashboardSummary } from '@/widgets/dashboard/summary'
import { RecentTasks } from '@/widgets/dashboard/recent-tasks'
import { RecentTaskLists } from '@/widgets/dashboard/recent-task-lists'

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

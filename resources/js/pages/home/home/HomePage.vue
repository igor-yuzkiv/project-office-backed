<script setup lang="ts">
import { useDashboardQuery } from '@/entities/dashboard'
import { DashboardSummary } from '@/widgets/home-dashboard/summary'
import { RecentTasks, RecentTaskLists } from '@/widgets/home-dashboard/recent'

const { summary, recentTasks, recentTaskLists, isPending, isError, refetch } = useDashboardQuery()
</script>

<template>
    <div class="gap-4 p-4 flex flex-1 flex-col overflow-auto">
        <DashboardSummary :summary="summary" :is-pending="isPending" :is-error="isError" @retry="refetch()" />

        <div class="gap-4 xl:grid-cols-2 grid">
            <RecentTasks :tasks="recentTasks" :is-pending="isPending" :is-error="isError" @retry="refetch()" />
            <RecentTaskLists
                :task-lists="recentTaskLists"
                :is-pending="isPending"
                :is-error="isError"
                @retry="refetch()"
            />
        </div>
    </div>
</template>

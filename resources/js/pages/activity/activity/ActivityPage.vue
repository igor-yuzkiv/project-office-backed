<script setup lang="ts">
import { PageHead } from '@/shared/components/page-head'
import { ActivityStream } from '@/widgets/audit-trail/activity-stream'
import { useBreadcrumbs } from '@/app/shell'

useBreadcrumbs([{ label: 'Activity' }])
</script>

<template>
    <div class="gap-2 p-3 flex flex-1 flex-col overflow-hidden">
        <PageHead title="Activity" class="px-1" />
        <ActivityStream class="min-h-0 flex-1" />
    </div>
</template>

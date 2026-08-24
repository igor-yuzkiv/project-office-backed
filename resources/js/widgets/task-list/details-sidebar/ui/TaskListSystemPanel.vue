<script setup lang="ts">
import { DisplayField } from '@/shared/components/display'
import { UserAvatar } from '@/widgets/user/user-avatar'
import { formatDateTime } from '@/shared/utils/date.util'
import type { ITaskList } from '@/entities/task-list/types'

defineProps<{ taskList: ITaskList }>()
</script>

<template>
    <section class="gap-2 flex flex-col">
        <h2 class="font-semibold text-surface-900 dark:text-surface-0">System</h2>

        <DisplayField v-if="taskList.created_by" label="Created By">
            <div class="gap-2 flex items-center">
                <UserAvatar
                    :initials="taskList.created_by.initials"
                    :avatar-url="taskList.created_by.avatar_url"
                    size="small"
                />
                <span class="text-surface-700 dark:text-surface-300">{{ taskList.created_by.name }}</span>
            </div>
        </DisplayField>

        <DisplayField label="Created At" :value="formatDateTime(taskList.created_at)" />

        <DisplayField v-if="taskList.updated_by" label="Updated By">
            <div class="gap-2 flex items-center">
                <UserAvatar
                    :initials="taskList.updated_by.initials"
                    :avatar-url="taskList.updated_by.avatar_url"
                    size="small"
                />
                <span class="text-surface-700 dark:text-surface-300">{{ taskList.updated_by.name }}</span>
            </div>
        </DisplayField>

        <DisplayField label="Updated At" :value="formatDateTime(taskList.updated_at)" />
    </section>
</template>

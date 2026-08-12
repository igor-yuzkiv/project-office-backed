<script setup lang="ts">
import { computed } from 'vue'
import Button from 'primevue/button'
import { useRouter } from 'vue-router'
import { useTaskListTasksQuery } from '@/entities/task-list/queries'

const props = defineProps<{ taskListId: string; currentTaskId: string }>()

const router = useRouter()

// The same query key the panel above uses, so this reads its cache instead of asking again.
const { tasks } = useTaskListTasksQuery(() => props.taskListId)

// -1 when the current task is not on the loaded page, which leaves both buttons disabled rather
// than guessing at neighbours nobody fetched.
const currentIndex = computed(() => tasks.value.findIndex((task) => task.id === props.currentTaskId))

const previousTask = computed(() => (currentIndex.value > 0 ? tasks.value[currentIndex.value - 1] : null))

const nextTask = computed(() =>
    currentIndex.value >= 0 && currentIndex.value < tasks.value.length - 1 ? tasks.value[currentIndex.value + 1] : null
)

function goTo(taskId: string) {
    router.push({ name: 'task-details', params: { id: taskId } })
}
</script>

<template>
    <nav class="gap-2 flex items-center justify-between">
        <Button
            label="Previous task"
            icon="pi pi-arrow-left"
            severity="secondary"
            text
            size="small"
            :disabled="!previousTask"
            :title="previousTask?.name"
            @click="previousTask && goTo(previousTask.id)"
        />

        <Button
            label="Next task"
            icon="pi pi-arrow-right"
            icon-pos="right"
            severity="secondary"
            text
            size="small"
            :disabled="!nextTask"
            :title="nextTask?.name"
            @click="nextTask && goTo(nextTask.id)"
        />
    </nav>
</template>

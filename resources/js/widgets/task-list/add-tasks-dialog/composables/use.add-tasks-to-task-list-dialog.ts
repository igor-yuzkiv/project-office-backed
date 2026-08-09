import { computed, ref, watch } from 'vue'
import { refDebounced } from '@vueuse/core'
import { PAGE_SIZE } from '@/app/config'
import { useTasksSearchQuery } from '@/entities/task/queries'
import { useAddTasksToTaskListMutation } from '@/entities/task-list/mutations'
import type { TaskOverviewDto, TaskSearchParams } from '@/entities/task/types'
import type { FilterPayloadItem } from '@/shared/filters'
import { ApiError } from '@/shared/api/api.error'
import { useToast } from '@/shared/composables'

/**
 * Candidates are read page by page and filtered on the backend — the full set of a project's
 * tasks never reaches the browser, and Save only adds, leaving existing associations alone.
 */
export function useAddTasksToTaskListDialog(taskListId: () => string, projectId: () => string | undefined) {
    const toast = useToast()

    const visible = ref(false)
    const selected = ref<TaskOverviewDto[]>([])
    const searchQuery = ref('')
    const page = ref(1)

    const debouncedSearchQuery = refDebounced(
        computed(() => searchQuery.value.trim()),
        300
    )

    const searchParams = computed<TaskSearchParams>(() => ({
        query: debouncedSearchQuery.value,
        filters: [
            {
                filter_key: 'lookup',
                field_name: 'project_id',
                value: projectId() ?? '',
                matchMode: 'equals',
                params: {},
            } satisfies FilterPayloadItem,
            {
                filter_key: 'nullable',
                field_name: 'task_list_id',
                value: null,
                matchMode: 'equals',
                params: {},
            } satisfies FilterPayloadItem,
        ],
        page: page.value,
        per_page: PAGE_SIZE,
    }))

    const {
        tasks: candidates,
        paginationMeta,
        isPending,
    } = useTasksSearchQuery(searchParams, { enabled: computed(() => visible.value && !!projectId()) })

    const { mutate: addTasks, isPending: isSaving } = useAddTasksToTaskListMutation(taskListId)

    const canSave = computed(() => selected.value.length > 0)

    function open() {
        selected.value = []
        searchQuery.value = ''
        page.value = 1
        visible.value = true
    }

    function close() {
        visible.value = false
    }

    function onPageChange(newPage: number) {
        page.value = newPage
    }

    function submit() {
        if (!canSave.value) return

        addTasks(
            selected.value.map((task) => task.id),
            {
                onSuccess: close,
                onError: (error) => {
                    toast.error(error instanceof ApiError ? error.displayMessage : 'Failed to add tasks.')
                },
            }
        )
    }

    // Paging on a stale page after a search would show an empty table, so a new search restarts.
    watch(debouncedSearchQuery, () => {
        page.value = 1
    })

    return {
        visible,
        selected,
        searchQuery,
        page,
        candidates,
        paginationMeta,
        isPending,
        isSaving,
        canSave,
        open,
        close,
        onPageChange,
        submit,
    }
}

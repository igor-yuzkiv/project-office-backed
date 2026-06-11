import { computed, ref } from 'vue'
import { useCreateProjectMutation } from '@/entities/project/mutations'
import { useUpdateProjectMutation } from '@/entities/project/mutations'
import type { IProject, ProjectStatusValue } from '@/entities/project/types'
import { ApiError } from '@/shared/api/api.error'
import type { LaravelValidationErrors } from '@/shared/types'

export function useProjectUpsertDialog() {
    const visible = ref(false)
    const editingProject = ref<IProject | undefined>()
    const mode = computed<'create' | 'update'>(() => (editingProject.value ? 'update' : 'create'))

    const name = ref('')
    const status = ref<ProjectStatusValue>('draft')
    const validationErrors = ref<LaravelValidationErrors>({})

    const { mutate: create, isPending: isCreating } = useCreateProjectMutation()
    const { mutate: update, isPending: isUpdating } = useUpdateProjectMutation()
    const isPending = computed(() => isCreating.value || isUpdating.value)

    function open(project?: IProject) {
        editingProject.value = project
        name.value = project?.name ?? ''
        status.value = project?.status ?? 'draft'
        validationErrors.value = {}
        visible.value = true
    }

    function close() {
        visible.value = false
    }

    function handleError(error: unknown) {
        if (error instanceof ApiError && error.isValidationError) {
            validationErrors.value = error.validationErrors ?? {}
        }
    }

    function submit() {
        validationErrors.value = {}

        if (mode.value === 'create') {
            create({ name: name.value, status: status.value }, { onSuccess: close, onError: handleError })
        } else if (editingProject.value) {
            update(
                { id: editingProject.value.id, data: { name: name.value, status: status.value } },
                { onSuccess: close, onError: handleError }
            )
        }
    }

    return { visible, mode, name, status, validationErrors, isPending, open, close, submit }
}

import { computed, ref } from 'vue'
import { useCreateProjectMutation } from '@/entities/project/mutations'
import { useUpdateProjectMutation } from '@/entities/project/mutations'
import type { IProject, ProjectStatusValue } from '@/entities/project/types'
import type { ITag } from '@/entities/tag/types'
import { ApiError } from '@/shared/api/api.error'
import type { LaravelValidationErrors } from '@/shared/types'

export interface ProjectUpsertFormData {
    name: string
    status: ProjectStatusValue
    tags: ITag[]
}

function getDefaultFormData(): ProjectUpsertFormData {
    return { name: '', status: 'draft', tags: [] }
}

export function useProjectUpsertDialog() {
    const visible = ref(false)
    const editingProject = ref<IProject | undefined>()
    const mode = computed<'create' | 'update'>(() => (editingProject.value ? 'update' : 'create'))

    const formData = ref<ProjectUpsertFormData>(getDefaultFormData())
    const validationErrors = ref<LaravelValidationErrors>({})

    const { mutate: create, isPending: isCreating } = useCreateProjectMutation()
    const { mutate: update, isPending: isUpdating } = useUpdateProjectMutation()
    const isPending = computed(() => isCreating.value || isUpdating.value)

    function open(project?: IProject) {
        editingProject.value = project
        formData.value = {
            name: project?.name ?? '',
            status: project?.status ?? 'draft',
            tags: project?.tags ?? [],
        }
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

        const tag_ids = formData.value.tags.map((t) => t.id)

        if (mode.value === 'create') {
            create(
                { name: formData.value.name, status: formData.value.status, tag_ids },
                { onSuccess: close, onError: handleError }
            )
        } else if (editingProject.value) {
            update(
                {
                    id: editingProject.value.id,
                    data: { name: formData.value.name, status: formData.value.status, tag_ids },
                },
                { onSuccess: close, onError: handleError }
            )
        }
    }

    return { visible, mode, formData, validationErrors, isPending, open, close, submit }
}

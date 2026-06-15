<script setup lang="ts">
import { ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { MarkdownEditor } from '@/shared/components/md-editor'
import { InputContainer } from '@/shared/components/input'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import DatePicker from 'primevue/datepicker'
import { projectStatusOptions, PROJECT_MODULE_NAME, PROJECT_ATTACHMENT_ROLES } from '@/entities/project/config'
import { useProjectQuery } from '@/entities/project/queries'
import { useUpdateProjectMutation } from '@/entities/project/mutations'
import type { IUpdateProjectInput, ProjectStatusValue } from '@/entities/project/types'
import type { ITag } from '@/entities/tag/types'
import { ApiError } from '@/shared/api/api.error'
import type { LaravelValidationErrors } from '@/shared/types'
import { useToast } from '@/shared/composables'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import { useHeaderActions, useBreadcrumbs } from '@/app/shell'
import { TagList } from '@/widgets/tags/metadata'
import { ManageRecordTagsDialog } from '@/widgets/tags/manage-dialog'
import { IconButton } from '@/shared/components/button'

interface ProjectEditFormData {
    name: string
    status: ProjectStatusValue
    description: string
    start_date: Date | null
    end_date: Date | null
    tags: ITag[]
}

const route = useRoute()
const router = useRouter()
const layoutStore = useAppLayoutStore()
const toast = useToast()
const projectId = route.params.id as string
const { project, isError } = useProjectQuery(projectId)
const { mutate: updateProject } = useUpdateProjectMutation()

const formData = ref<ProjectEditFormData>({
    name: '',
    status: 'draft',
    description: '',
    start_date: null,
    end_date: null,
    tags: [],
})
const isFormInitialized = ref(false)
const validationErrors = ref<LaravelValidationErrors>({})
const showManageTagsDialog = ref(false)

function handleError(error: unknown) {
    if (error instanceof ApiError && error.isValidationError) {
        validationErrors.value = error.validationErrors ?? {}
    } else {
        toast.error(error instanceof ApiError ? error.displayMessage : 'Failed to save project.')
    }
}

function navigateBack() {
    if (window.history.state?.back) {
        router.back()
    } else {
        router.push({ name: 'project-details', params: { id: projectId } })
    }
}

function formatDateForApi(date: Date | null): string | null {
    if (!date) return null
    return date.toISOString().split('T')[0]
}

function submit() {
    if (!project.value) return

    validationErrors.value = {}

    const input: IUpdateProjectInput = {
        name: formData.value.name,
        status: formData.value.status,
        description: formData.value.description || null,
        start_date: formatDateForApi(formData.value.start_date),
        end_date: formatDateForApi(formData.value.end_date),
        tag_ids: formData.value.tags.map((t) => t.id),
    }

    updateProject(
        { id: projectId, data: input },
        {
            onSuccess: navigateBack,
            onError: handleError,
        }
    )
}

watch(isError, (error) => {
    if (error) toast.error('Failed to load project.')
})

watch(
    project,
    (p) => {
        if (p && !isFormInitialized.value) {
            formData.value = {
                name: p.name,
                status: p.status,
                description: p.description ?? '',
                start_date: p.start_date ? new Date(p.start_date) : null,
                end_date: p.end_date ? new Date(p.end_date) : null,
                tags: p.tags ?? [],
            }
            isFormInitialized.value = true
            layoutStore.setPageTitle(`${p.prefix} | ${p.name}`)
        }
    },
    { immediate: true }
)

useHeaderActions([
    { key: 'save-project', title: 'Save', action: submit, is_primary: true },
    { key: 'cancel-project', title: 'Cancel', action: navigateBack },
])

useBreadcrumbs(() => [
    { label: 'Projects', to: { name: 'projects' } },
    { label: project.value?.name ?? 'Project', to: { name: 'project-details', params: { id: projectId } } },
    { label: 'Edit' },
])
</script>

<template>
    <div v-if="project" class="p-2 flex flex-1 flex-col overflow-hidden">
        <div class="p-3 gap-3 flex flex-col">
            <div class="md:grid-cols-2 gap-3 grid grid-cols-1">
                <InputContainer label="Name" :error="validationErrors.name" required>
                    <InputText
                        v-model="formData.name"
                        placeholder="Project name..."
                        :invalid="!!validationErrors.name"
                    />
                </InputContainer>

                <InputContainer label="Status" :error="validationErrors.status">
                    <Select
                        v-model="formData.status"
                        :options="projectStatusOptions()"
                        option-label="label"
                        option-value="value"
                        :invalid="!!validationErrors.status"
                    />
                </InputContainer>

                <div class="md:grid-cols-2 gap-3 md:col-span-2 col-span-full grid grid-cols-1">
                    <InputContainer label="Start Date" :error="validationErrors.start_date">
                        <DatePicker
                            v-model="formData.start_date"
                            date-format="yy-mm-dd"
                            show-clear
                            :invalid="!!validationErrors.start_date"
                        />
                    </InputContainer>

                    <InputContainer label="End Date" :error="validationErrors.end_date">
                        <DatePicker
                            v-model="formData.end_date"
                            date-format="yy-mm-dd"
                            show-clear
                            :invalid="!!validationErrors.end_date"
                        />
                    </InputContainer>
                </div>
            </div>

            <InputContainer label="Tags" :error="validationErrors.tag_ids">
                <div class="gap-2 p-1 flex items-center">
                    <IconButton
                        size="medium"
                        severity="success"
                        icon="mdi:tag-edit"
                        @click="showManageTagsDialog = true"
                    />
                    <TagList :tags="formData.tags" />
                </div>
            </InputContainer>
        </div>

        <div class="flex-1 overflow-auto">
            <MarkdownEditor
                v-model="formData.description"
                preview
                style="height: 100%"
                :image_entity_type="PROJECT_MODULE_NAME"
                :image_entity_id="projectId"
                :image_role="PROJECT_ATTACHMENT_ROLES.DESCRIPTION"
            />
        </div>

        <ManageRecordTagsDialog v-model:visible="showManageTagsDialog" v-model="formData.tags" />
    </div>
</template>

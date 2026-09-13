<script setup lang="ts">
import { TITLE_INPUT_PT } from '@/shared/components/title-input'
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { MarkdownEditor } from '@/shared/components/md-editor'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import DatePicker from 'primevue/datepicker'
import Button from 'primevue/button'
import { projectStatusOptions, ProjectAttachmentRoles } from '@/entities/project/config'
import { uploadProjectAttachmentRequest } from '@/entities/project/api'
import { useProjectQuery } from '@/entities/project/queries'
import { useUpdateProjectMutation } from '@/entities/project/mutations'
import type { IUpdateProjectInput, ProjectStatusValue } from '@/entities/project/types'
import type { ITag } from '@/entities/tag/types'
import { ApiError } from '@/shared/api/api.error'
import type { LaravelValidationErrors } from '@/shared/types'
import { useToast } from '@/shared/composables'
import { useAppLayoutStore } from '@/app/stores/use.app-layout.store'
import { useBreadcrumbs } from '@/app/shell'
import { CopyableKey, PropertiesGrid } from '@/shared/components/display'
import { IconPickerField } from '@/shared/components/icon-picker'
import { TagList } from '@/widgets/tags/metadata'
import { ManageRecordTagsDialog } from '@/widgets/tags/manage-dialog'
import { ProjectStatusTag } from '@/widgets/projects/status-tag'

interface ProjectEditFormData {
    name: string
    icon: string | null
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
const { mutate: updateProject, isPending: isSaving } = useUpdateProjectMutation()

const formData = ref<ProjectEditFormData>({
    name: '',
    icon: null,
    status: 'draft',
    description: '',
    start_date: null,
    end_date: null,
    tags: [],
})
const savedSnapshot = ref<string | null>(null)
const validationErrors = ref<LaravelValidationErrors>({})
const showManageTagsDialog = ref(false)

const isDirty = computed(() => savedSnapshot.value !== null && snapshot(formData.value) !== savedSnapshot.value)

function snapshot(data: ProjectEditFormData): string {
    return JSON.stringify({
        name: data.name,
        icon: data.icon,
        status: data.status,
        description: data.description,
        startDate: formatDateForApi(data.start_date),
        endDate: formatDateForApi(data.end_date),
        tagIds: data.tags.map((tag) => tag.id).sort(),
    })
}

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

async function handleImageUpload(files: File[], callback: (urls: string[]) => void) {
    const results = await Promise.all(
        files.map((file) => uploadProjectAttachmentRequest(projectId, file, ProjectAttachmentRoles.DESCRIPTION))
    )
    callback(results.map((res) => res.data.url))
}

function submit() {
    if (!project.value) return

    validationErrors.value = {}

    const input: IUpdateProjectInput = {
        name: formData.value.name,
        icon: formData.value.icon,
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
        if (p && savedSnapshot.value === null) {
            formData.value = {
                name: p.name,
                icon: p.icon,
                status: p.status,
                description: p.description ?? '',
                start_date: p.start_date ? new Date(p.start_date) : null,
                end_date: p.end_date ? new Date(p.end_date) : null,
                tags: p.tags ?? [],
            }
            savedSnapshot.value = snapshot(formData.value)
            layoutStore.setPageTitle(`${p.prefix} | ${p.name}`)
        }
    },
    { immediate: true }
)

useBreadcrumbs(() => [
    { label: 'Projects', to: { name: 'projects' } },
    { label: project.value?.name ?? 'Project', to: { name: 'project-details', params: { id: projectId } } },
    { label: 'Edit' },
])
</script>

<template>
    <div v-if="project" class="min-h-0 flex flex-1 flex-col overflow-auto">
        <form class="page-container min-h-0 pb-6 flex flex-1 flex-col" @submit.prevent="submit">
            <div class="type-meta gap-1 flex items-center">
                <CopyableKey :value="project.prefix" />
                <div class="gap-1 ml-auto flex items-center">
                    <span v-if="isDirty" class="type-meta-3 pr-1.5 whitespace-nowrap">Unsaved changes</span>
                    <Button
                        label="Cancel"
                        size="small"
                        severity="secondary"
                        outlined
                        type="button"
                        @click="navigateBack"
                    />
                    <Button label="Save" size="small" type="submit" :loading="isSaving" />
                </div>
            </div>

            <InputText
                v-model="formData.name"
                placeholder="Project name"
                aria-label="Project name"
                :invalid="!!validationErrors.name"
                :pt="TITLE_INPUT_PT"
            />
            <p v-if="validationErrors.name" class="text-red-500 -mt-2 mb-3 text-[12.5px]">
                {{ validationErrors.name[0] }}
            </p>

            <PropertiesGrid>
                <span>Status</span>
                <div>
                    <Select
                        v-model="formData.status"
                        :options="projectStatusOptions()"
                        option-label="label"
                        option-value="value"
                        size="small"
                        class="min-w-[180px]"
                        :invalid="!!validationErrors.status"
                    >
                        <template #value="{ value }">
                            <ProjectStatusTag :status="value" />
                        </template>
                        <template #option="{ option }">
                            <ProjectStatusTag :status="option.value" />
                        </template>
                    </Select>
                    <span v-if="validationErrors.status" class="text-red-500 basis-full text-[12.5px]">
                        {{ validationErrors.status[0] }}
                    </span>
                </div>

                <span>Dates</span>
                <div>
                    <DatePicker
                        v-model="formData.start_date"
                        date-format="yy-mm-dd"
                        placeholder="Start"
                        show-clear
                        size="small"
                        class="w-[150px]"
                        :invalid="!!validationErrors.start_date"
                    />
                    <span class="text-ink-3">→</span>
                    <DatePicker
                        v-model="formData.end_date"
                        date-format="yy-mm-dd"
                        placeholder="End"
                        show-clear
                        size="small"
                        class="w-[150px]"
                        :invalid="!!validationErrors.end_date"
                    />
                    <span v-if="validationErrors.start_date" class="text-red-500 basis-full text-[12.5px]">
                        {{ validationErrors.start_date[0] }}
                    </span>
                    <span v-if="validationErrors.end_date" class="text-red-500 basis-full text-[12.5px]">
                        {{ validationErrors.end_date[0] }}
                    </span>
                </div>

                <span>Icon</span>
                <div>
                    <IconPickerField v-model="formData.icon" />
                    <span v-if="validationErrors.icon" class="text-red-500 basis-full text-[12.5px]">
                        {{ validationErrors.icon[0] }}
                    </span>
                </div>

                <span>Tags</span>
                <div>
                    <TagList :tags="formData.tags" inline />
                    <Button
                        label="Manage tags"
                        size="small"
                        severity="secondary"
                        outlined
                        type="button"
                        @click="showManageTagsDialog = true"
                    />
                    <span v-if="validationErrors.tag_ids" class="text-red-500 basis-full text-[12.5px]">
                        {{ validationErrors.tag_ids[0] }}
                    </span>
                </div>
            </PropertiesGrid>

            <hr class="border-line mt-5 mb-7" />

            <h2 class="type-section mb-2.5">Description</h2>
            <!-- The editor takes the rest of the viewport and scrolls inside; the page scrolls only when it cannot fit. -->
            <MarkdownEditor
                v-model="formData.description"
                preview
                class="min-h-[240px] flex-1"
                min-height="0"
                :handle-image-upload="handleImageUpload"
            />
            <p v-if="validationErrors.description" class="text-red-500 mt-1 text-[12.5px]">
                {{ validationErrors.description[0] }}
            </p>
        </form>

        <ManageRecordTagsDialog v-model:visible="showManageTagsDialog" v-model="formData.tags" />
    </div>
</template>

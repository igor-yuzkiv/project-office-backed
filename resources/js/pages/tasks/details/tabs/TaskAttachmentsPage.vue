<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'
import Menu from 'primevue/menu'
import type { MenuItem } from 'primevue/menuitem'
import { PAGE_SIZE } from '@/app/config'
import { TaskAttachmentRoles } from '@/entities/task/config'
import { useTaskAttachmentsQuery } from '@/entities/task/queries'
import { useUploadTaskAttachmentMutation } from '@/entities/task/mutations'
import { useDeleteAttachmentMutation } from '@/entities/attachment/mutations'
import { ApiError } from '@/shared/api'
import { useToast } from '@/shared/composables'
import type { IAttachment } from '@/entities/attachment/types'
import { IconButton } from '@/shared/components/button'
import { AttachmentsTableView } from '@/widgets/attachments/views/table'
import { AttachmentDropZone, UploadAttachmentButton } from '@/widgets/attachments/attachment-uploader'

const route = useRoute()

const taskId = computed(() => route.params.id as string)

const page = ref(1)

const pagination = computed(() => ({ page: page.value, per_page: PAGE_SIZE }))

const { attachments, paginationMeta, isPending } = useTaskAttachmentsQuery(taskId, pagination)

const toast = useToast()
const { mutate: uploadAttachment, isPending: isUploading } = useUploadTaskAttachmentMutation(taskId)

function upload(file: File) {
    uploadAttachment(
        { file, role: TaskAttachmentRoles.UPLOAD },
        {
            onSuccess: () => toast.success('File uploaded successfully.'),
            onError: (error) => {
                toast.error(error instanceof ApiError ? error.displayMessage : 'Failed to upload file.')
            },
        }
    )
}

const { mutateWithConfirm: deleteAttachment } = useDeleteAttachmentMutation()

const rowMenu = ref<InstanceType<typeof Menu>>()
const selectedAttachment = ref<IAttachment>()

const rowMenuItems: MenuItem[] = [
    {
        label: 'Download',
        icon: 'pi pi-download',
        command: () => window.open(selectedAttachment.value!.url, '_blank', 'noopener,noreferrer'),
    },
    {
        label: 'Delete',
        icon: 'pi pi-trash',
        command: () =>
            deleteAttachment(
                selectedAttachment.value!.id,
                `Are you sure you want to delete "${selectedAttachment.value!.original_name}"?`
            ),
    },
]

function openRowMenu(event: MouseEvent, attachment: IAttachment) {
    selectedAttachment.value = attachment
    rowMenu.value?.toggle(event)
}

function onPageChange(newPage: number) {
    page.value = newPage
}
</script>

<template>
    <div class="flex flex-1 flex-col overflow-hidden">
        <div class="flex h-full w-full flex-col overflow-hidden">
            <div class="gap-2 p-1 flex items-center justify-end">
                <UploadAttachmentButton :is-uploading="isUploading" @file-selected="upload" />
            </div>

            <AttachmentDropZone :is-uploading="isUploading" @file-drop="upload">
                <AttachmentsTableView
                    :attachments="attachments"
                    :is-pending="isPending"
                    :pagination-meta="paginationMeta"
                    :page="page"
                    @page-change="onPageChange"
                >
                    <template #actions="{ row }">
                        <IconButton
                            severity="secondary"
                            icon="pepicons-pop:dots-y"
                            @click.stop="openRowMenu($event, row)"
                        />
                    </template>
                </AttachmentsTableView>
            </AttachmentDropZone>
        </div>

        <Menu ref="rowMenu" :model="rowMenuItems" popup />
    </div>
</template>

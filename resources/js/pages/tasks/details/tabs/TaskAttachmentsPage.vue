<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'
import { PAGE_SIZE } from '@/app/config'
import { TASK_ATTACHMENT_ROLES, TASK_MODULE_NAME } from '@/entities/task/config'
import { useAttachmentsSearchQuery } from '@/entities/attachment/queries'
import { AttachmentsTable } from '@/widgets/attachments/views/table'
import { AttachmentDropZone, UploadAttachmentButton } from '@/widgets/attachments/attachment-uploader'

const route = useRoute()

const taskId = computed(() => route.params.id as string)

const page = ref(1)

const searchParams = computed(() => ({
    filters: [
        { filter_key: 'text', field_name: 'entity_type', value: TASK_MODULE_NAME, matchMode: 'equals', params: {} },
        { filter_key: 'lookup', field_name: 'entity_id', value: taskId.value, matchMode: 'equals', params: {} },
    ],
    page: page.value,
    per_page: PAGE_SIZE,
}))

const { attachments, paginationMeta, isPending } = useAttachmentsSearchQuery(searchParams)

function onPageChange(newPage: number) {
    page.value = newPage
}
</script>

<template>
    <div class="flex flex-1 flex-col overflow-hidden">
        <div class="app-card flex h-full w-full flex-col overflow-hidden">
            <div class="gap-2 p-1 flex items-center justify-end">
                <UploadAttachmentButton
                    :entity-type="TASK_MODULE_NAME"
                    :entity-id="taskId"
                    :role="TASK_ATTACHMENT_ROLES.UPLOAD"
                />
            </div>

            <AttachmentDropZone :entity-type="TASK_MODULE_NAME" :entity-id="taskId">
                <AttachmentsTable
                    :attachments="attachments"
                    :is-pending="isPending"
                    :pagination-meta="paginationMeta"
                    :page="page"
                    @page-change="onPageChange"
                />
            </AttachmentDropZone>
        </div>
    </div>
</template>

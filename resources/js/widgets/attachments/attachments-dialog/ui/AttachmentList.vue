<script setup lang="ts">
import Button from 'primevue/button'
import type { IAttachment } from '@/entities/attachment/types'
import { IconButton } from '@/shared/components/button'
import { formatFileSize } from '@/shared/utils/file.util'
import { formatDate } from '@/shared/utils/date.util'

defineProps<{
    attachments: IAttachment[]
    isPending: boolean
}>()

const emit = defineEmits<{
    (e: 'download', attachment: IAttachment): void
    (e: 'delete', attachment: IAttachment): void
}>()

function fileMeta(attachment: IAttachment): string {
    return [
        formatFileSize(attachment.size_bytes),
        attachment.created_by?.name,
        formatDate(attachment.created_at, 'MMM d, HH:mm'),
    ]
        .filter(Boolean)
        .join(' · ')
}
</script>

<template>
    <p v-if="isPending" class="type-meta-3 px-2 py-3">Loading files...</p>

    <p v-else-if="attachments.length === 0" class="type-meta-3 px-2 py-3">No files yet.</p>

    <ul v-else class="flex flex-col">
        <li
            v-for="attachment in attachments"
            :key="attachment.id"
            class="group gap-3 px-2 py-2 hover:bg-hover rounded-md grid grid-cols-[34px_1fr_auto] items-center"
        >
            <span
                class="bg-code-bg text-ink-2 font-semibold tracking-wide rounded-md grid h-[34px] w-[34px] place-items-center text-[10px] uppercase"
            >
                {{ attachment.extension ?? 'file' }}
            </span>

            <span class="min-w-0">
                <span class="type-body text-ink font-medium block truncate">{{ attachment.original_name }}</span>
                <span class="type-meta-3 block">{{ fileMeta(attachment) }}</span>
            </span>

            <span class="gap-0.5 flex items-center opacity-0 group-hover:opacity-100 focus-within:opacity-100">
                <Button label="Download" severity="secondary" size="small" text @click="emit('download', attachment)" />
                <IconButton icon="heroicons:trash" aria-label="Delete" @click="emit('delete', attachment)" />
            </span>
        </li>
    </ul>
</template>

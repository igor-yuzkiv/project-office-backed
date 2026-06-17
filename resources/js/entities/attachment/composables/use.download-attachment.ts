import { ref } from 'vue'
import type { IAttachment } from '../types'
import { downloadAttachmentRequest } from '../api'

export function useDownloadAttachment() {
    const isPending = ref(false)

    async function download(attachment: IAttachment): Promise<void> {
        isPending.value = true
        try {
            const blob = await downloadAttachmentRequest(attachment.id)
            const url = URL.createObjectURL(blob)
            const anchor = document.createElement('a')
            anchor.href = url
            anchor.download = attachment.original_name
            anchor.click()
            URL.revokeObjectURL(url)
        } finally {
            isPending.value = false
        }
    }

    return { download, isPending }
}

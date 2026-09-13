import { beforeEach, describe, expect, it, vi } from 'vitest'
import { ref } from 'vue'
import type { PaginationMeta } from '@/shared/types'
import type { IAttachment } from '../types'
import { useAttachmentsDialog } from './use.attachments-dialog'

const attachments = ref([{ id: 'a1', original_name: 'spec.pdf' } as IAttachment])
const paginationMeta = ref<PaginationMeta | undefined>({ total: 3 } as PaginationMeta)
const mutateAsync = vi.fn()
const mutateWithConfirm = vi.fn()
const download = vi.fn()
const toast = { success: vi.fn(), error: vi.fn() }

vi.mock('@/shared/composables/use.toast', () => ({ useToast: () => toast }))
vi.mock('../mutations', () => ({ useDeleteAttachmentMutation: () => ({ mutateWithConfirm }) }))
vi.mock('./use.download-attachment', () => ({ useDownloadAttachment: () => ({ download }) }))

function dialog() {
    return useAttachmentsDialog(
        { attachments, paginationMeta, isPending: ref(false) },
        { mutateAsync, isPending: ref(false) },
        'tasks.upload'
    )
}

describe('useAttachmentsDialog', () => {
    beforeEach(() => {
        vi.clearAllMocks()
        paginationMeta.value = { total: 3 } as PaginationMeta
    })

    it('counts from the pagination total and falls back to the loaded list', () => {
        const { count } = dialog()

        expect(count.value).toBe(3)
        paginationMeta.value = undefined
        expect(count.value).toBe(1)
    })

    it('uploads under the given role and reports success', async () => {
        mutateAsync.mockResolvedValue(undefined)
        const file = new File(['x'], 'x.txt')

        await dialog().upload(file)

        expect(mutateAsync).toHaveBeenCalledWith({ file, role: 'tasks.upload' })
        expect(toast.success).toHaveBeenCalled()
    })

    it('reports a failed upload', async () => {
        mutateAsync.mockRejectedValue(new Error('boom'))

        await dialog().upload(new File(['x'], 'x.txt'))

        expect(toast.error).toHaveBeenCalledWith('Failed to upload file.')
    })

    it('deletes after confirming by file name', () => {
        dialog().remove({ id: 'a1', original_name: 'spec.pdf' } as IAttachment)

        expect(mutateWithConfirm).toHaveBeenCalledWith('a1', 'Are you sure you want to delete "spec.pdf"?')
    })
})

import { beforeEach, describe, expect, it, vi } from 'vitest'
import { ref } from 'vue'
import { useTaskAttachmentsDialog } from './use.task-attachments-dialog'

const attachments = ref([{ id: 'a1', original_name: 'spec.pdf' }])
const paginationMeta = ref<{ total: number } | undefined>({ total: 3 })
const mutateAsync = vi.fn()
const mutateWithConfirm = vi.fn()
const download = vi.fn()
const toast = { success: vi.fn(), error: vi.fn() }

vi.mock('@/shared/composables/use.toast', () => ({ useToast: () => toast }))
vi.mock('@/entities/attachment/mutations', () => ({
    useDeleteAttachmentMutation: () => ({ mutateWithConfirm }),
}))
vi.mock('@/entities/attachment/composables', () => ({ useDownloadAttachment: () => ({ download }) }))
vi.mock('../queries/use.task-attachments.query', () => ({
    useTaskAttachmentsQuery: () => ({ attachments, paginationMeta, isPending: ref(false) }),
}))
vi.mock('../mutations/use.upload-task-attachment.mutation', () => ({
    useUploadTaskAttachmentMutation: () => ({ mutateAsync, isPending: ref(false) }),
}))

describe('useTaskAttachmentsDialog', () => {
    beforeEach(() => {
        vi.clearAllMocks()
        paginationMeta.value = { total: 3 }
    })

    it('counts from the pagination total and falls back to the loaded list', () => {
        const { count } = useTaskAttachmentsDialog('t1')

        expect(count.value).toBe(3)
        paginationMeta.value = undefined
        expect(count.value).toBe(1)
    })

    it('uploads with the task upload role and reports success', async () => {
        mutateAsync.mockResolvedValue(undefined)
        const file = new File(['x'], 'x.txt')

        await useTaskAttachmentsDialog('t1').upload(file)

        expect(mutateAsync).toHaveBeenCalledWith({ file, role: 'tasks.upload' })
        expect(toast.success).toHaveBeenCalled()
    })

    it('reports a failed upload', async () => {
        mutateAsync.mockRejectedValue(new Error('boom'))

        await useTaskAttachmentsDialog('t1').upload(new File(['x'], 'x.txt'))

        expect(toast.error).toHaveBeenCalledWith('Failed to upload file.')
    })

    it('deletes after confirming by file name', () => {
        useTaskAttachmentsDialog('t1').remove({ id: 'a1', original_name: 'spec.pdf' } as never)

        expect(mutateWithConfirm).toHaveBeenCalledWith('a1', 'Are you sure you want to delete "spec.pdf"?')
    })
})

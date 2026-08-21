import { computed, ref, watch, type MaybeRefOrGetter, toValue } from 'vue'
import {
    ProjectDocumentAttachmentRoles,
    uploadProjectDocumentAttachmentRequest,
    useUpdateProjectDocumentMutation,
} from '@/entities/project-document'
import type {
    IProjectDocument,
    IUpdateProjectDocumentInput,
    ProjectDocumentStatusValue,
} from '@/entities/project-document/types'
import type { ITag } from '@/entities/tag/types'
import { ApiError } from '@/shared/api/api.error'
import { useConfirmDialog, useToast } from '@/shared/composables'
import type { LaravelValidationErrors } from '@/shared/types'

export interface ProjectDocumentEditingOptions {
    // The tree keeps its own snapshots of the rows, so a saved title reaches it
    // only when its owner is told to reload.
    onSaved?: () => void
}

export interface ProjectDocumentDraft {
    title: string
    content: string
    status: ProjectDocumentStatusValue
    tags: ITag[]
}

function draftFrom(document: IProjectDocument): ProjectDocumentDraft {
    return {
        title: document.title,
        content: document.content ?? '',
        status: document.status,
        tags: [...(document.tags ?? [])],
    }
}

function sameTags(left: ITag[], right: ITag[]): boolean {
    return left.length === right.length && left.every((tag, index) => tag.id === right[index]?.id)
}

/**
 * Editing a document in place. The draft is deliberately separate from the loaded
 * document: nothing the user types reaches the tree, the path strip or the details
 * panel until the server has accepted it.
 */
export function useProjectDocumentEditing(
    document: MaybeRefOrGetter<IProjectDocument | undefined>,
    options: ProjectDocumentEditingOptions = {}
) {
    const toast = useToast()
    const confirm = useConfirmDialog()

    const isEditing = ref(false)
    const draft = ref<ProjectDocumentDraft>({ title: '', content: '', status: 'draft', tags: [] })
    const validationErrors = ref<LaravelValidationErrors>({})

    const { mutate: update, isPending: isSaving } = useUpdateProjectDocumentMutation()

    const isDirty = computed(() => {
        const current = toValue(document)

        if (!isEditing.value || !current) return false

        return (
            draft.value.title !== current.title ||
            draft.value.content !== (current.content ?? '') ||
            draft.value.status !== current.status ||
            !sameTags(draft.value.tags, current.tags ?? [])
        )
    })

    function start() {
        const current = toValue(document)

        if (!current) return

        draft.value = draftFrom(current)
        validationErrors.value = {}
        isEditing.value = true
    }

    function cancel() {
        isEditing.value = false
        validationErrors.value = {}
    }

    function save() {
        const current = toValue(document)

        if (!current || isSaving.value) return

        // A document has to keep a name. An empty title is refused rather than sent,
        // and the heading goes back to the name the server still knows.
        if (!draft.value.title.trim()) {
            draft.value.title = current.title
            toast.warn('A document title cannot be empty.')

            return
        }

        validationErrors.value = {}

        const input: IUpdateProjectDocumentInput = {
            title: draft.value.title.trim(),
            content: draft.value.content,
            status: draft.value.status,
            tag_ids: draft.value.tags.map((tag) => tag.id),
        }

        update(
            { id: current.id, data: input },
            {
                onSuccess: () => {
                    isEditing.value = false
                    toast.success('Document saved.')
                    options.onSaved?.()
                },
                onError: (error: unknown) => {
                    // The draft survives a failed save: the user keeps what they typed.
                    if (error instanceof ApiError && error.isValidationError) {
                        validationErrors.value = error.validationErrors ?? {}
                        // Nothing in this screen renders field errors, so a rejected
                        // save has to say so out loud or it reads as a no-op.
                        toast.error(Object.values(validationErrors.value).flat()[0] ?? 'Document could not be saved.')
                    } else {
                        toast.error(error instanceof ApiError ? error.displayMessage : 'Failed to save document.')
                    }
                },
            }
        )
    }

    async function confirmDiscard(): Promise<boolean> {
        if (!isDirty.value) return true

        const discarded = await confirm.requireAsync({
            header: 'Unsaved changes',
            message: 'This document has changes that were never saved. Leave and lose them?',
            acceptLabel: 'Discard changes',
            rejectLabel: 'Keep editing',
        })

        if (discarded) cancel()

        return discarded
    }

    async function handleContentImageUpload(files: File[], callback: (urls: string[]) => void) {
        const current = toValue(document)

        if (!current) return

        const results = await Promise.all(
            files.map((file) =>
                uploadProjectDocumentAttachmentRequest(current.id, file, ProjectDocumentAttachmentRoles.CONTENT)
            )
        )

        callback(results.map((result) => result.data.url))
    }

    // Opening another document leaves editing behind; the guard asks before this runs.
    watch(
        () => toValue(document)?.id,
        () => cancel()
    )

    return {
        isEditing,
        isDirty,
        isSaving,
        draft,
        validationErrors,
        start,
        cancel,
        save,
        confirmDiscard,
        handleContentImageUpload,
    }
}

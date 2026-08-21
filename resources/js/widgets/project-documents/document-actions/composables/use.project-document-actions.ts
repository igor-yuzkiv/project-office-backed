import { computed, type MaybeRefOrGetter, toValue } from 'vue'
import { useDeleteProjectDocumentMutation } from '@/entities/project-document'
import type { IProjectDocument } from '@/entities/project-document/types'
import { useProjectDocumentMove } from '@/widgets/project-documents/move-dialog'

export interface ProjectDocumentActionsOptions {
    onMoved?: () => void
    onDeleted?: (document: IProjectDocument) => void
}

/**
 * What a surface can do with an opened document. It states the intent — where Edit
 * and Annotation mode lead, how Move and Delete run — and leaves it to the page to
 * decide where those land, because a header action is the shell's shape, not a widget's.
 */
export function useProjectDocumentActions(
    document: MaybeRefOrGetter<IProjectDocument | undefined>,
    options: ProjectDocumentActionsOptions = {}
) {
    const documentId = () => toValue(document)?.id ?? ''

    const moveDialog = useProjectDocumentMove(documentId, { onMoved: () => options.onMoved?.() })
    const { mutateWithConfirm: deleteDocument } = useDeleteProjectDocumentMutation()

    const editRoute = computed(() => ({ name: 'project-document-edit', params: { id: documentId() } }))

    // Annotating needs something to annotate.
    const annotationRoute = computed(() =>
        toValue(document)?.content ? { name: 'project-document-annotations', params: { id: documentId() } } : null
    )

    function remove() {
        const current = toValue(document)

        if (!current) return

        deleteDocument(current.id, current.title, () => options.onDeleted?.(current))
    }

    return { editRoute, annotationRoute, moveDialog, remove }
}

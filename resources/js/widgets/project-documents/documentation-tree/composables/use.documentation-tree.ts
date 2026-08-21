import { computed, ref, type MaybeRefOrGetter, toValue } from 'vue'
import {
    canProjectDocumentHaveChildren,
    projectDocumentDeleteConfirmMessage,
    useDeleteProjectDocumentMutation,
} from '@/entities/project-document'
import type { IProjectDocument, ProjectDocumentTreeNodeDto } from '@/entities/project-document/types'
import { useProjectDocumentCreateDialog } from '@/widgets/project-documents/create-dialog'
import { PROJECT_DOCUMENT_TREE_ROOT_KEY, useProjectDocumentTree } from '@/widgets/project-documents/views/tree-table'

export interface DocumentationTreeNodeRow {
    kind: 'node'
    key: string
    depth: number
    document: ProjectDocumentTreeNodeDto
    isExpanded: boolean
}

export interface DocumentationTreeLoadMoreRow {
    kind: 'load-more'
    key: string
    depth: number
    levelKey: string
    remaining: number
    isLoading: boolean
}

export type DocumentationTreeRow = DocumentationTreeNodeRow | DocumentationTreeLoadMoreRow

export interface DocumentationTreeCallbacks {
    onCreated?: (document: IProjectDocument) => void
    onDeleted?: (documentId: string) => void
}

export function useDocumentationTree(projectId: MaybeRefOrGetter<string>, callbacks: DocumentationTreeCallbacks = {}) {
    const tree = useProjectDocumentTree(projectId)
    const { mutateWithConfirm: deleteDocument } = useDeleteProjectDocumentMutation()

    const isPending = ref(false)
    const isError = ref(false)

    const createDialog = useProjectDocumentCreateDialog({
        onCreated: async (document) => {
            if (document.parent_id) {
                await tree.expandNode(document.parent_id)
            }

            await reload()
            callbacks.onCreated?.(document)
        },
    })

    const rows = computed<DocumentationTreeRow[]>(() => {
        const collected: DocumentationTreeRow[] = []
        collectRows(PROJECT_DOCUMENT_TREE_ROOT_KEY, 0, collected)

        return collected
    })

    const isEmpty = computed(() => rows.value.length === 0)

    function collectRows(levelKey: string, depth: number, collected: DocumentationTreeRow[]) {
        for (const document of tree.levelRows(levelKey)) {
            const isExpanded = tree.isLevelExpanded(document.id)
            collected.push({ kind: 'node', key: document.id, depth, document, isExpanded })

            if (isExpanded) {
                collectRows(document.id, depth + 1, collected)
            }
        }

        const remaining = tree.levelRemainingCount(levelKey)

        if (remaining > 0) {
            collected.push({
                kind: 'load-more',
                key: `load-more:${levelKey}`,
                depth,
                levelKey,
                remaining,
                isLoading: tree.isLevelLoading(levelKey),
            })
        }
    }

    async function load() {
        isPending.value = true
        isError.value = false

        try {
            await tree.loadRoot()
        } catch {
            isError.value = true
        } finally {
            isPending.value = false
        }
    }

    // A failed reload leaves the tree showing what it already had rather than
    // replacing a working tree with an error state.
    async function reload() {
        try {
            await tree.reloadLoadedLevels()
        } catch {
            isError.value = rows.value.length === 0
        }
    }

    async function toggleNode(document: ProjectDocumentTreeNodeDto) {
        if (tree.isLevelExpanded(document.id)) {
            tree.collapseNode(document.id)
            return
        }

        await tree.expandNode(document.id)
    }

    // Opening a document by URL has to reveal it: its ancestors are expanded from
    // the root down, each level loading before the next one can be found in it.
    async function expandAncestors(ancestorIds: string[]) {
        for (const ancestorId of ancestorIds) {
            await tree.expandNode(ancestorId)
        }
    }

    function loadMore(levelKey: string) {
        return tree.loadMoreLevel(levelKey)
    }

    function createRootDocument() {
        createDialog.open(toValue(projectId))
    }

    function createChildDocument(document: ProjectDocumentTreeNodeDto) {
        createDialog.open(toValue(projectId), { id: document.id, key: document.key, title: document.title })
    }

    function canCreateChildDocument(document: ProjectDocumentTreeNodeDto): boolean {
        return canProjectDocumentHaveChildren(document.depth)
    }

    function deleteNodeDocument(document: ProjectDocumentTreeNodeDto) {
        return deleteDocument(document.id, projectDocumentDeleteConfirmMessage(document.title), async () => {
            await reload()
            callbacks.onDeleted?.(document.id)
        })
    }

    return {
        rows,
        isPending,
        isError,
        isEmpty,
        createDialog,
        load,
        reload,
        toggleNode,
        expandAncestors,
        loadMore,
        createRootDocument,
        createChildDocument,
        canCreateChildDocument,
        deleteNodeDocument,
    }
}

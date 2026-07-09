import { computed, reactive, type MaybeRefOrGetter, toValue } from 'vue'
import { useQueryClient } from '@tanstack/vue-query'
import { fetchProjectDocumentTreeRequest } from '@/entities/project-document/api'
import { ProjectDocumentQueryKey } from '@/entities/project-document/config'
import type { ProjectDocumentTreeNodeDto } from '@/entities/project-document/types'
import type { PaginationMeta } from '@/shared/types'
import type { FilterPayloadItem } from '@/shared/filters'
import type { EntityTreeNode } from '@/shared/components/table'
import { PAGE_SIZE } from '@/app/config'

type DocumentTreeNode = EntityTreeNode<ProjectDocumentTreeNodeDto>

const ROOT_KEY = '__root__'

interface LevelState {
    rows: ProjectDocumentTreeNodeDto[]
    paginationMeta?: PaginationMeta
    page: number
    isLoading: boolean
    isExpanded: boolean
}

function createLevelState(isExpanded: boolean): LevelState {
    return { rows: [], page: 1, isLoading: false, isExpanded }
}

/**
 * Owns the lazy, paginated document tree state for a project. Reusable across any
 * consumer that embeds `<ProjectDocumentsTable>` (project documentation tab today,
 * a task's linked-documents tab in the future via `filters`).
 */
export function useProjectDocumentTree(
    projectId: MaybeRefOrGetter<string>,
    filters?: MaybeRefOrGetter<FilterPayloadItem[]>
) {
    const levels = reactive(new Map<string, LevelState>())

    const queryClient = useQueryClient()

    const treeNodes = computed<DocumentTreeNode[]>(() => getLevel(ROOT_KEY).rows.map(buildNode))
    const paginationMeta = computed(() => getLevel(ROOT_KEY).paginationMeta)
    const page = computed(() => getLevel(ROOT_KEY).page)
    const isPending = computed(() => getLevel(ROOT_KEY).isLoading)
    // PrimeVue's TreeTable only renders a node's children when its own key is marked
    // expanded here — it does NOT infer that from `node.children` being populated.
    // Without this, programmatic expansion (expandAllOnPage) fetches data that never
    // becomes visible, since PrimeVue's internal expanded-state was never told about it.
    const expandedKeys = computed<Record<string, boolean>>(() => {
        const keys: Record<string, boolean> = {}
        levels.forEach((level, key) => {
            if (key !== ROOT_KEY) {
                keys[key] = level.isExpanded
            }
        })
        return keys
    })

    function getLevel(key: string): LevelState {
        if (!levels.has(key)) {
            levels.set(key, createLevelState(key === ROOT_KEY))
        }

        // Always re-read through the reactive Map's `get` so callers receive the
        // proxy-wrapped value — mutating the raw object returned at creation time
        // would silently bypass Vue's reactivity and never trigger a re-render.
        return levels.get(key) as LevelState
    }

    function buildNode(row: ProjectDocumentTreeNodeDto): DocumentTreeNode {
        const level = levels.get(row.id)

        return {
            key: row.id,
            data: row,
            leaf: !row.has_children,
            children: level?.isExpanded ? level.rows.map(buildNode) : undefined,
        }
    }

    async function fetchLevel(key: string, parentId: string | null, page: number) {
        const level = getLevel(key)
        level.isLoading = true

        const resolvedProjectId = toValue(projectId)
        const resolvedFilters = toValue(filters)

        try {
            const response = await queryClient.fetchQuery({
                queryKey: ProjectDocumentQueryKey.tree(resolvedProjectId, {
                    parent_id: parentId,
                    page,
                    per_page: PAGE_SIZE,
                    filters: resolvedFilters,
                }),
                queryFn: () =>
                    fetchProjectDocumentTreeRequest(resolvedProjectId, {
                        parent_id: parentId,
                        page,
                        per_page: PAGE_SIZE,
                        filters: resolvedFilters,
                    }),
            })

            level.rows = response.data
            level.paginationMeta = response.meta
            level.page = page
        } finally {
            level.isLoading = false
        }
    }

    async function loadRoot(page = 1) {
        await fetchLevel(ROOT_KEY, null, page)
    }

    async function expandNode(nodeId: string) {
        const level = getLevel(nodeId)
        level.isExpanded = true

        if (level.rows.length === 0) {
            await fetchLevel(nodeId, nodeId, 1)
        }
    }

    function collapseNode(nodeId: string) {
        getLevel(nodeId).isExpanded = false
    }

    async function expandAllOnPage() {
        const root = getLevel(ROOT_KEY)
        await Promise.all(root.rows.filter((row) => row.has_children).map((row) => expandNode(row.id)))
    }

    return {
        treeNodes,
        paginationMeta,
        page,
        isPending,
        expandedKeys,
        loadRoot,
        expandNode,
        collapseNode,
        expandAllOnPage,
    }
}

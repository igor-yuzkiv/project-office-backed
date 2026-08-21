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

export { ROOT_KEY as PROJECT_DOCUMENT_TREE_ROOT_KEY }

interface LevelState {
    rows: ProjectDocumentTreeNodeDto[]
    paginationMeta?: PaginationMeta
    page: number
    isLoading: boolean
    isExpanded: boolean
}

type LevelFetchMode = 'replace' | 'append'

function createLevelState(isExpanded: boolean): LevelState {
    return { rows: [], page: 1, isLoading: false, isExpanded }
}

export function useProjectDocumentTree(
    projectId: MaybeRefOrGetter<string>,
    filters?: MaybeRefOrGetter<FilterPayloadItem[]>,
    rootParentId?: MaybeRefOrGetter<string | null>
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

    async function fetchLevel(key: string, parentId: string | null, page: number, mode: LevelFetchMode = 'replace') {
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

            level.rows = mode === 'append' ? [...level.rows, ...response.data] : response.data
            level.paginationMeta = response.meta
            level.page = page
        } finally {
            level.isLoading = false
        }
    }

    async function loadRoot(page = 1) {
        await fetchLevel(ROOT_KEY, toValue(rootParentId) ?? null, page)
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

    // A level's parent is the node it hangs from; the root level hangs from
    // `rootParentId`, which is null for a project's own root documents.
    function levelParentId(key: string): string | null {
        return key === ROOT_KEY ? (toValue(rootParentId) ?? null) : key
    }

    // The readers below go through `levels.get` rather than `getLevel`: they are
    // meant to be called from computed properties, and `getLevel` writes to the
    // reactive Map when a level is missing.
    function levelMeta(key: string): PaginationMeta | undefined {
        return levels.get(key)?.paginationMeta
    }

    function levelRows(key: string): ProjectDocumentTreeNodeDto[] {
        return levels.get(key)?.rows ?? []
    }

    function levelRemainingCount(key: string): number {
        const level = levels.get(key)

        return level?.paginationMeta ? Math.max(level.paginationMeta.total - level.rows.length, 0) : 0
    }

    function isLevelLoading(key: string): boolean {
        return levels.get(key)?.isLoading ?? false
    }

    function isLevelExpanded(key: string): boolean {
        return levels.get(key)?.isExpanded ?? false
    }

    // Appends the next page to what is already shown, unlike `loadRoot`, which
    // swaps the page for the paginated TreeTable view.
    async function loadMoreLevel(key: string) {
        const level = getLevel(key)

        if (level.isLoading || levelRemainingCount(key) === 0) {
            return
        }

        await fetchLevel(key, levelParentId(key), level.page + 1, 'append')
    }

    // Re-reads every page a level has accumulated, so a level stays as long as
    // the user made it after a document was created, deleted or moved.
    async function reloadLevel(key: string) {
        const lastPage = getLevel(key).page

        await fetchLevel(key, levelParentId(key), 1)

        for (let page = 2; page <= lastPage; page++) {
            await fetchLevel(key, levelParentId(key), page, 'append')
        }
    }

    async function reloadLoadedLevels() {
        const loadedKeys = [...levels.keys()].filter((key) => getLevel(key).rows.length > 0)

        await Promise.all(loadedKeys.map(reloadLevel))
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
        levelMeta,
        levelRows,
        levelRemainingCount,
        isLevelLoading,
        isLevelExpanded,
        loadMoreLevel,
        reloadLevel,
        reloadLoadedLevels,
    }
}

import { computed, ref, watch } from 'vue'
import { useAuditRecordsQuery } from '../queries'
import type { AuditRecordDto } from '../types'

/** The screen holds roughly fifteen rows, so a page of twenty always fills it. */
const FEED_PAGE_SIZE = 20

/**
 * The feed as one growing list. Pages are page-numbered, not infinite-query based, and are merged
 * here rather than in the query cache: the cache keeps pages apart on purpose, and stitching them
 * inside it would mean a custom select over every key.
 */
export function useAuditRecordFeed() {
    const page = ref(1)
    const records = ref<AuditRecordDto[]>([])

    const params = computed(() => ({ page: page.value, per_page: FEED_PAGE_SIZE }))
    const {
        records: pageRecords,
        paginationMeta,
        isPending,
        isError,
        isFetching,
        refetch,
    } = useAuditRecordsQuery(params)

    // immediate: a page already in the query cache is there before the watcher is registered
    // (staleTime is five minutes), and without this the feed would render empty on a warm cache.
    watch(
        pageRecords,
        (incoming) => {
            if (incoming.length === 0) {
                return
            }

            // A new event between two requests shifts the offsets, so a row from the end of one page
            // can arrive again at the start of the next.
            const seen = new Set(records.value.map((record) => record.id))
            const merged = [...records.value, ...incoming.filter((record) => !seen.has(record.id))]

            // Ids are ULIDs, so sorting by id descending is the same chronology the backend serves.
            // Without it a page refetched later would append fresh events below older ones.
            records.value = merged.sort((a, b) => b.id.localeCompare(a.id))
        },
        { immediate: true }
    )

    const hasMore = computed(() => {
        // A failed page leaves no meta behind, which would otherwise read as the end of the feed.
        // The widget shows the error instead, and 'load more' is not the answer to it.
        if (isError.value) {
            return false
        }

        const meta = paginationMeta.value

        // Taken from meta, not from the list length: after deduplication the length is short by
        // exactly the rows that were duplicated.
        return meta ? meta.current_page < meta.last_page : false
    })

    function loadMore() {
        const meta = paginationMeta.value

        if (!hasMore.value || isFetching.value || meta === undefined) {
            return
        }

        // The page we asked for last must have arrived. Neither isPending nor isFetching covers
        // this: two clicks in the same tick both read the flags of the settled previous page —
        // reactivity has not run yet — and page would jump 1 → 3, dropping a page for good,
        // because loadMore only ever counts upward.
        if (page.value !== meta.current_page) {
            return
        }

        page.value += 1
    }

    return { records, hasMore, loadMore, isPending, isFetching, isError, refetch }
}

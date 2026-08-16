import { formatDistanceToNowStrict, isYesterday } from 'date-fns'

const MINUTE = 60_000
const HOUR = 60 * MINUTE
const DAY = 24 * HOUR

/**
 * Compact "last touched" time for dashboard rows: minutes and hours while the change is still
 * fresh, day names once it is not. Falls back instead of throwing, so one bad timestamp cannot
 * take a whole panel down.
 */
export function formatRelativeTime(value: string): string {
    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return 'Unknown'
    }

    const elapsed = Date.now() - date.getTime()

    if (elapsed < MINUTE) {
        return 'Just now'
    }

    if (elapsed < HOUR) {
        return `${Math.floor(elapsed / MINUTE)}m ago`
    }

    if (elapsed < DAY && !isYesterday(date)) {
        return `${Math.floor(elapsed / HOUR)}h ago`
    }

    if (isYesterday(date)) {
        return 'Yesterday'
    }

    return formatDistanceToNowStrict(date, { addSuffix: true, unit: 'day' })
}

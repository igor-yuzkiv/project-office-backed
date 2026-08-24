import { isToday, isYesterday } from 'date-fns'
import { formatDate } from '@/shared/utils/date.util'
import type { AuditRecordDto } from '@/entities/audit-trail'

export type ActivityDayGroup = {
    key: string
    label: string
    records: AuditRecordDto[]
}

/**
 * Splits an already ordered feed into day groups, in the reader's own timezone — the same one
 * DisplayDate renders in, so a row's heading and its timestamp never disagree.
 */
export function groupRecordsByDay(records: AuditRecordDto[]): ActivityDayGroup[] {
    const groups: ActivityDayGroup[] = []

    for (const record of records) {
        const date = new Date(record.created_at)
        // An unparseable timestamp gets its own heading rather than throwing during render.
        const key = formatDate(date, 'yyyy-MM-dd') ?? 'unknown'

        if (groups.at(-1)?.key !== key) {
            groups.push({ key, label: dayLabel(date), records: [] })
        }

        groups.at(-1)!.records.push(record)
    }

    return groups
}

function dayLabel(date: Date): string {
    if (Number.isNaN(date.getTime())) {
        return 'Unknown date'
    }

    if (isToday(date)) {
        return 'Today'
    }

    if (isYesterday(date)) {
        return 'Yesterday'
    }

    return formatDate(date, 'MMMM d, yyyy') ?? 'Unknown date'
}

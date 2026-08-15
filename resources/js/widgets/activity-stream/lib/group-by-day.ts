import { format, isToday, isYesterday } from 'date-fns'
import type { AuditRecordDto } from '@/entities/audit-record'

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
        const key = format(date, 'yyyy-MM-dd')

        if (groups.at(-1)?.key !== key) {
            groups.push({ key, label: dayLabel(date), records: [] })
        }

        groups.at(-1)!.records.push(record)
    }

    return groups
}

function dayLabel(date: Date): string {
    if (isToday(date)) {
        return 'Today'
    }

    if (isYesterday(date)) {
        return 'Yesterday'
    }

    return format(date, 'MMMM d, yyyy')
}

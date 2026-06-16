import { format as dateFnsFormat } from 'date-fns'

export function formatDate(date: string | Date | null | undefined, fmt = 'MMM d, yyyy'): string | null {
    if (!date) return null
    try {
        return dateFnsFormat(new Date(date), fmt)
    } catch {
        return null
    }
}

export function formatDateTime(date: string | Date | null | undefined): string | null {
    return formatDate(date, 'MMM d, yyyy HH:mm')
}

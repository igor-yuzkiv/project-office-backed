export type TitleSegment = { text: string; isKey: boolean }

/**
 * Cuts a stored title around the subject's key so the key alone can become a link. The first
 * occurrence only: a key is a whole token, never a part of a longer one (MTM-1 inside MTM-12).
 */
export function splitTitleByKey(title: string, key: string | null): TitleSegment[] {
    if (!key) {
        return [{ text: title, isKey: false }]
    }

    const pattern = new RegExp(`(^|[^\\w-])(${escapeRegExp(key)})(?![\\w-])`)
    const match = pattern.exec(title)

    if (match === null) {
        return [{ text: title, isKey: false }]
    }

    const start = match.index + match[1].length
    const end = start + key.length

    return [
        { text: title.slice(0, start), isKey: false },
        { text: key, isKey: true },
        { text: title.slice(end), isKey: false },
    ].filter((segment) => segment.text !== '')
}

function escapeRegExp(value: string): string {
    return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
}

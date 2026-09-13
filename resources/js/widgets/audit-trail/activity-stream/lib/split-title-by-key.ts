export type TitleSegment = { text: string; isKey: boolean }

/**
 * Cuts a stored title around the subject so that part alone can become a link: a task title
 * carries the key, the other titles carry the name in guillemets. The first occurrence only: a
 * key is a whole token, never a part of a longer one (MTM-1 inside MTM-12).
 */
export function splitTitleByKey(title: string, key: string | null, name: string | null = null): TitleSegment[] {
    const byKey = key ? new RegExp(`(^|[^\\w-])(${escapeRegExp(key)})(?![\\w-])`).exec(title) : null
    const byName = byKey === null && name ? new RegExp(`(«)(${escapeRegExp(name)})(»)`).exec(title) : null
    const match = byKey ?? byName

    if (match === null) {
        return [{ text: title, isKey: false }]
    }

    const start = match.index + match[1].length
    const end = start + match[2].length

    return [
        { text: title.slice(0, start), isKey: false },
        { text: match[2], isKey: true },
        { text: title.slice(end), isKey: false },
    ].filter((segment) => segment.text !== '')
}

function escapeRegExp(value: string): string {
    return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
}

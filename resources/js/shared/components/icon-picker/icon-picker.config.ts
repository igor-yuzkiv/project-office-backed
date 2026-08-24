/** The icon set the picker searches. Everything shown here comes from one family, so a board of projects looks like one board. */
export const ICON_SET_PREFIX = 'tabler'

/** Where a reader goes to find a name this picker cannot search for them. */
export const ICON_CATALOGUE_URL = 'https://icon-sets.iconify.design/'

export const ICON_SEARCH_ENDPOINT = 'https://api.iconify.design/search'

export const ICON_SEARCH_LIMIT = 48

/**
 * What the grid shows before anyone types. Not a catalogue and not a curated set — just a place
 * to start, covering the kinds of things projects usually are.
 */
export const STARTER_ICONS = [
    'tabler:rocket',
    'tabler:briefcase',
    'tabler:building',
    'tabler:code',
    'tabler:terminal-2',
    'tabler:database',
    'tabler:server',
    'tabler:cloud',
    'tabler:device-mobile',
    'tabler:world',
    'tabler:palette',
    'tabler:pencil',
    'tabler:file-text',
    'tabler:book',
    'tabler:clipboard-check',
    'tabler:calendar',
    'tabler:chart-line',
    'tabler:target',
    'tabler:bulb',
    'tabler:flask',
    'tabler:shield-lock',
    'tabler:users',
    'tabler:message-circle',
    'tabler:shopping-cart',
] as const

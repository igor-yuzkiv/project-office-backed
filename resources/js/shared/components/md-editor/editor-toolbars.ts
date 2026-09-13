import type { ToolbarNames } from 'md-editor-v3'

// Write / Preview / Split is not here — MarkdownEditor appends its own segment for that.
export const DEFAULT_TOOLBARS: ToolbarNames[] = [
    'bold',
    'underline',
    'italic',
    '-',
    'strikeThrough',
    'title',
    'sub',
    'sup',
    'quote',
    'unorderedList',
    'orderedList',
    'task',
    '-',
    'codeRow',
    'code',
    'link',
    'table',
    'image',
    '-',
    'revoke',
    'next',
    '=',
    'pageFullscreen',
    'fullscreen',
]

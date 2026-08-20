import type { BlockAnchor } from '@/shared/utils/markdown-anchor.util'

export interface AnnotationAuthor {
    id: string
    name: string
    initials: string
    avatar_url: string | null
}

export interface IAnnotation {
    id: string
    content: string
    text_snapshot: string | null
    anchor: BlockAnchor
    author: AnnotationAuthor
    created_at: string
    updated_at: string
}

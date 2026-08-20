import type { BlockAnchor } from '@/shared/utils/markdown-anchor.util'

/** One shape for creating and updating: the WebApi writes the whole object. */
export interface SaveAnnotationDto {
    content: string
    text_snapshot: string | null
    anchor: BlockAnchor
}

import type { UserOverviewDto } from '@/entities/user/types'
import type { BlockAnchor } from '@/shared/utils/markdown-anchor.util'

export interface IAnnotation {
    id: string
    content: string
    text_snapshot: string | null
    anchor: BlockAnchor
    author: UserOverviewDto
    created_at: string
    updated_at: string
}

import type { HexColor, IEntity } from '@/shared/types'

export interface ITag extends IEntity {
    id: string
    name: string
    color: HexColor
}

export interface CreateTagPayload {
    name: string
    color?: HexColor
}

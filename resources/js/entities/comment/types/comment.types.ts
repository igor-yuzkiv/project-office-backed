export interface CommentAuthor {
    id: string
    name: string
    initials: string
    avatar_url: string | null
}

export interface CommentCan {
    update: boolean
    delete: boolean
}

export interface IComment {
    id: string
    content: string
    author: CommentAuthor
    created_at: string
    updated_at: string
    can: CommentCan
}

export interface CommentAuthor {
    id: string
    name: string
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

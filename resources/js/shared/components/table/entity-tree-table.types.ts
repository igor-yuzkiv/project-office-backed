import type { TreeNode } from 'primevue/treenode'

export interface EntityTreeTableColumnDef {
    field: string
    header: string
    style?: string
    expander?: boolean
}

export type EntityTreeNode<T> = TreeNode & { data: T }

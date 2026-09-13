import type { SortFieldDef } from '@/shared/sort'
import type { EntityTableColumnDef } from '@/shared/components/table'

export const taskListSortFieldDefs: SortFieldDef[] = [
    { field: 'name', label: 'Name' },
    { field: 'status', label: 'Status' },
    { field: 'created_at', label: 'Created' },
    { field: 'updated_at', label: 'Updated' },
]

export const taskListTableColumnDefs: EntityTableColumnDef[] = [
    { field: 'key', header: 'Key', style: 'width: 8rem' },
    { field: 'name', header: 'List', style: 'min-width: 20rem' },
    { field: 'project', header: 'Project', style: 'min-width: 12rem' },
    { field: 'status', header: 'Status', style: 'min-width: 9rem' },
    { field: 'progress', header: 'Progress', style: 'width: 10rem' },
    { field: 'updated_at', header: 'Updated', style: 'min-width: 10rem' },
]

export function taskListTableColumnsExcluding(...fields: string[]): EntityTableColumnDef[] {
    return taskListTableColumnDefs.filter((column) => !fields.includes(column.field))
}

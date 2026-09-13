import type { SortFieldDef } from '@/shared/sort'
import type { EntityTableColumnDef } from '@/shared/components/table'

export const taskSortFieldDefs: SortFieldDef[] = [
    { field: 'name', label: 'Name' },
    { field: 'status', label: 'Status' },
    { field: 'priority', label: 'Priority' },
    { field: 'created_at', label: 'Created' },
    { field: 'updated_at', label: 'Updated' },
]

export const taskTableColumnDefs: EntityTableColumnDef[] = [
    { field: 'key', header: 'Key', style: 'width: 7rem' },
    { field: 'name', header: 'Task', style: 'min-width: 24rem' },
    { field: 'project', header: 'Project', style: 'min-width: 11rem' },
    { field: 'task_list.name', header: 'Task list', style: 'min-width: 11rem' },
    { field: 'status', header: 'Status', style: 'min-width: 9rem' },
    { field: 'priority', header: 'Priority', style: 'min-width: 7rem' },
    { field: 'tags', header: 'Tags', style: 'min-width: 10rem' },
    { field: 'updated_at', header: 'Updated', style: 'min-width: 9rem' },
]

export function taskTableColumnsExcluding(...fields: string[]): EntityTableColumnDef[] {
    return taskTableColumnDefs.filter((column) => !fields.includes(column.field))
}

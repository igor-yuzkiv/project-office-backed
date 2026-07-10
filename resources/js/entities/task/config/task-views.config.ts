import type { SortFieldDef } from '@/shared/sort'
import type { EntityTableColumnDef } from '@/shared/components/table'

export const taskSortFieldDefs: SortFieldDef[] = [
    { field: 'name', label: 'Name' },
    { field: 'status', label: 'Status' },
    { field: 'priority', label: 'Priority' },
    { field: 'created_at', label: 'Created' },
    { field: 'updated_at', label: 'Updated' },
]

// All available task table columns. Pages render the full set or drop the ones that don't
// apply to their context via taskTableColumnsExcluding().
export const taskTableColumnDefs: EntityTableColumnDef[] = [
    { field: 'key', header: 'Key', style: 'min-width: 10rem' },
    { field: 'status', header: 'Status', style: 'min-width: 9rem' },
    { field: 'name', header: 'Task Name', style: 'min-width: 30rem' },
    { field: 'project', header: 'Project', style: 'min-width: 15rem' },
    { field: 'task_list.name', header: 'Task List', style: 'min-width: 15rem' },
    { field: 'priority', header: 'Priority', style: 'min-width: 7rem' },
    { field: 'tags', header: 'Tags', style: 'min-width: 12rem' },
    { field: 'updated_by', header: 'Updated By', style: 'min-width: 12rem' },
]

export function taskTableColumnsExcluding(...fields: string[]): EntityTableColumnDef[] {
    return taskTableColumnDefs.filter((column) => !fields.includes(column.field))
}

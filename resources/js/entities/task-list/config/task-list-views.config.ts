import type { EntityTableColumnDef } from '@/shared/components/table'

// All available task list table columns. Pages render the full set or drop the ones that
// don't apply to their context via taskListTableColumnsExcluding().
export const taskListTableColumnDefs: EntityTableColumnDef[] = [
    { field: 'key', header: 'Key', style: 'min-width: 10rem' },
    { field: 'name', header: 'Name', style: 'min-width: 25rem' },
    { field: 'status', header: 'Status', style: 'min-width: 9rem' },
    { field: 'project', header: 'Project', style: 'min-width: 15rem' },
    { field: 'tags', header: 'Tags', style: 'min-width: 12rem' },
    { field: 'tasks_count', header: 'Tasks', style: 'width: 8rem' },
]

export function taskListTableColumnsExcluding(...fields: string[]): EntityTableColumnDef[] {
    return taskListTableColumnDefs.filter((column) => !fields.includes(column.field))
}

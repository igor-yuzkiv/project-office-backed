import { createFilterDefMap } from '@/shared/filters'
import { ProjectLookupField } from '@/widgets/projects/lookup-field'
import { TaskListLookupField } from '@/widgets/task-list/lookup-field'

export function createDefaultTaskFiltersDefMap() {
    return createFilterDefMap((map) =>
        map
            .addField('name', 'text', (d) => d.label('Name'))
            .addField('status', 'text', (d) => d.label('Status'))
            .addField('priority', 'integer', (d) => d.label('Priority'))
            .addField('project_id', 'lookup', (d) =>
                d.label('Project').component(ProjectLookupField).withoutMatchMode()
            )
            .addField('task_list_id', 'lookup', (d) =>
                d.label('Task List').component(TaskListLookupField).withoutMatchMode()
            )
    )
}

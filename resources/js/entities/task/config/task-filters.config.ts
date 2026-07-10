import { createFilterDefMap } from '@/shared/filters'
import { taskPriorityOptions } from './task-priority.config'
import { taskStatusOptions } from './task-status.config'
import { ProjectLookupField } from '@/widgets/projects/lookup-field'
import { TaskListLookupField } from '@/widgets/task-list/lookup-field'

export function createDefaultTaskFiltersDefMap() {
    return createFilterDefMap((map) =>
        map
            .addField('name', 'text', (d) => d.label('Name'))
            .addField('status', 'select', (d) =>
                d.label('Status').matchMode('in').setInputProps({
                    options: taskStatusOptions(),
                    optionLabel: 'label',
                    optionValue: 'value',
                    placeholder: 'Select status',
                })
            )
            .addField('priority', 'select', (d) =>
                d.label('Priority').matchMode('in').setInputProps({
                    options: taskPriorityOptions(),
                    optionLabel: 'label',
                    optionValue: 'value',
                    placeholder: 'Select priority',
                })
            )
            .addField('project_id', 'lookup', (d) =>
                d.label('Project').component(ProjectLookupField).withoutMatchMode()
            )
            .addField('task_list_id', 'lookup', (d) =>
                d.label('Task List').component(TaskListLookupField).withoutMatchMode()
            )
            .addField('tags', 'tags', (d) => d.label('Tags'))
    )
}

import { createFilterDefMap } from '@/shared/filters'
import { ProjectLookupField } from '@/widgets/projects/lookup-field'
import { taskListStatusOptions } from './task-list-status.config'

export function createDefaultTaskListFiltersDefMap() {
    return createFilterDefMap((map) =>
        map
            .addField('name', 'text', (d) => d.label('Name'))
            .addField('status', 'select', (d) =>
                d.label('Status').matchMode('in').setInputProps({
                    options: taskListStatusOptions(),
                    optionLabel: 'label',
                    optionValue: 'value',
                    placeholder: 'Select status',
                })
            )
            .addField('project_id', 'lookup', (d) =>
                d.label('Project').component(ProjectLookupField).withoutMatchMode()
            )
            .addField('tags', 'tags', (d) => d.label('Tags'))
    )
}

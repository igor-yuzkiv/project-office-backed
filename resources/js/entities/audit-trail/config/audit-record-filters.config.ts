import { createFilterDefMap, type FilterPayloadItem } from '@/shared/filters'
import { ProjectLookupField } from '@/widgets/projects/lookup-field'
import type { AuditRecordFilters, KnownAuditRecordSubjectType } from '../types'
import { ACTIVITY_TYPE_LABELS } from './activity-type.registry'

export type ActivitySegment = 'all' | KnownAuditRecordSubjectType

export const ACTIVITY_SEGMENTS: { value: ActivitySegment; label: string }[] = [
    { value: 'all', label: 'All' },
    { value: 'task', label: 'Tasks' },
    { value: 'task_list', label: 'Lists' },
    { value: 'project_document', label: 'Documents' },
]

export function isActivitySegment(value: unknown): value is ActivitySegment {
    return ACTIVITY_SEGMENTS.some((segment) => segment.value === value)
}

/**
 * Both fields hide their match mode: the feed knows only "is" and "is any of". A page scoped to
 * one project leaves the project field out.
 */
export function createActivityFiltersDefMap({ withProject = true } = {}) {
    return createFilterDefMap((map) => {
        if (withProject) {
            map.addField('project_id', 'lookup', (d) =>
                d.label('Project').component(ProjectLookupField).withoutMatchMode()
            )
        }
        map.addField('type', 'select', (d) =>
            d
                .label('Type')
                .matchMode('in')
                .withoutMatchMode()
                .setInputProps({
                    options: Object.entries(ACTIVITY_TYPE_LABELS).map(([value, label]) => ({ value, label })),
                    optionLabel: 'label',
                    optionValue: 'value',
                    placeholder: 'Select types',
                    filter: true,
                })
        )
    })
}

/** The feed filters the chips and the segment stand for; the project scope is the page's. */
export function resolveActivityFilters(
    resolved: FilterPayloadItem[],
    segment: ActivitySegment
): Pick<AuditRecordFilters, 'type' | 'subject_type'> {
    const types = resolved.find((f) => f.field_name === 'type')?.value

    return {
        type: Array.isArray(types) ? (types as string[]) : undefined,
        subject_type: segment === 'all' ? undefined : [segment],
    }
}

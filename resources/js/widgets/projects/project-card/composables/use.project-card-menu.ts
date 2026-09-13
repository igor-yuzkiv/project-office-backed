import { computed, type MaybeRefOrGetter, toValue } from 'vue'
import type { MenuItem } from 'primevue/menuitem'
import type { ProjectOverviewDto } from '@/entities/project/types'
import { usePinProjectMutation, useUnpinProjectMutation } from '@/entities/project/mutations'

type ProjectCardMenuHandlers = {
    onEdit: (project: ProjectOverviewDto) => void
    onDelete: (project: ProjectOverviewDto) => void
}

/**
 * The `…` menu of a project everywhere it is listed — the card and the table share the same
 * items. Pin and unpin run here; edit and delete are the caller's, since they open dialogs.
 */
export function useProjectCardMenu(
    project: MaybeRefOrGetter<ProjectOverviewDto | undefined>,
    handlers: ProjectCardMenuHandlers
) {
    const pinMutation = usePinProjectMutation()
    const unpinMutation = useUnpinProjectMutation()

    const items = computed<MenuItem[]>(() => {
        const current = toValue(project)
        if (!current) return []

        return [
            { label: 'Edit', icon: 'pi pi-pencil', command: () => handlers.onEdit(current) },
            current.is_pinned
                ? { label: 'Unpin', icon: 'pi pi-bookmark-fill', command: () => unpinMutation.mutate(current.id) }
                : { label: 'Pin', icon: 'pi pi-bookmark', command: () => pinMutation.mutate(current.id) },
            { separator: true },
            { label: 'Delete', icon: 'pi pi-trash', command: () => handlers.onDelete(current) },
        ]
    })

    return { items }
}

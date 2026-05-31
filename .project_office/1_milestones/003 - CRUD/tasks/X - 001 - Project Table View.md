---
type: task
status: todo
milestone_number: 3
milestone_name: Frontend CRUD Initial
milestone_path: .project_office/1_milestones/003 - CRUD
task_number: 001
task_name: Project Table View
task_path: .project_office/1_milestones/003 - CRUD/tasks/001 - Project Table View.md
created_at: 2026-05-31
updated_at: 2026-05-31
---

# 001 - Project Table View

## Milestone

- Number: 3
- Name: Frontend CRUD Initial
- Path: `.project_office/1_milestones/003 - CRUD`
- Plan: `.project_office/1_milestones/003 - CRUD/plan.md`

## Task

- Number: 001
- Name: Project Table View
- Status: todo
- Path: `.project_office/1_milestones/003 - CRUD/tasks/001 - Project Table View.md`

## Опис

Підготувати початкове табличне представлення `Project` на frontend.

У межах цієї задачі потрібно реалізувати тільки table view для projects. Інші варіанти представлення поки не потрібні.

## Контекст

- API та типи для API вже визначені.
- Дизайн сторінки створення projects знаходиться тут:
  `.project_office/2_design/concept/create_project_page`
- Дизайн табличного представлення знаходиться тут:
  `.project_office/2_design/concept/projects_table_page.png`
- Наступні сторінки з формами мають бути однотипними. Відрізнятимуться тільки структура та логіка конкретної сутності.

## Вимоги

- Використати `PrimeVue DataTable` для таблиці projects.
- Реалізувати підтримку pagination.
- Sorting поки не реалізовувати.
- Filtering поки не реалізовувати.
- Створити query для отримання projects на рівні:
  `resources/js/entities/project/queries`
- Зберігати query keys на рівні:
  `resources/js/entities/project/config`
- У query варто створити computed для:
  - `projects`
  - `pagination`

## Приклад Query

```ts
import { computed, type MaybeRef, toValue } from 'vue'
import { keepPreviousData, useQuery } from '@tanstack/vue-query'
import type { ISignInstallerShift, LaravelPagingParams, Maybe } from '@artisan_direct_frontend/shared-types'
import { fetchRouteSignInstallerShifts } from '../../api/route.api.ts'
import { RouteQueryKey } from '../../config'

export function useRouteSignInstallerShiftsQuery(
    routeId: MaybeRef<Maybe<string>>,
    includePreviewShifts: MaybeRef<boolean> = false,
    pagination: MaybeRef<LaravelPagingParams> = { page: 1, per_page: 20 },
) {
    const { data, isPending, isError, refetch } = useQuery({
        queryKey: RouteQueryKey.signInstallerShifts(routeId, includePreviewShifts, pagination),
        queryFn: () =>
            fetchRouteSignInstallerShifts(
                toValue(routeId) as string,
                toValue(includePreviewShifts),
                toValue(pagination),
            ),
        placeholderData: keepPreviousData,
        enabled: computed(() => !!toValue(routeId)),
    })

    const shifts = computed<ISignInstallerShift[]>(() => data.value?.data ?? [])
    const meta = computed(() => data.value?.meta)

    return {
        shifts,
        meta,
        isPending,
        isError,
        refetch,
    }
}
```

## Приклад Query Keys

```ts
export const RouteQueryKey = {
    all: ['routes'] as const,
    paginated: (page: MaybeRefOrGetter<number>, per_page: MaybeRefOrGetter<number>) => {
        return [...RouteQueryKey.all, page, per_page]
    },
}
```

## Acceptance Criteria

- Projects page відображає projects у табличному вигляді.
- Дані отримуються через `@tanstack/vue-query`.
- Query для projects розміщена в `resources/js/entities/project/queries`.
- Query keys для projects розміщені в `resources/js/entities/project/config`.
- Таблиця використовує `PrimeVue DataTable`.
- Pagination працює через наявний API contract.
- Sorting і filtering не реалізовані в межах задачі.

---
type: task
status: todo
milestone_number: 3
milestone_name: Frontend CRUD Initial
milestone_path: .project_office/1_milestones/003 - CRUD
task_number: 002
task_name: Project Upsert Dialog
task_path: .project_office/1_milestones/003 - CRUD/tasks/002 - Project Upsert Dialog.md
created_at: 2026-05-31
updated_at: 2026-05-31
---

# 002 - Project Upsert Dialog

## Milestone

- Number: 3
- Name: Frontend CRUD Initial
- Path: `.project_office/1_milestones/003 - CRUD`
- Plan: `.project_office/1_milestones/003 - CRUD/plan.md`

## Task

- Number: 002
- Name: Project Upsert Dialog
- Status: todo
- Path: `.project_office/1_milestones/003 - CRUD/tasks/002 - Project Upsert Dialog.md`

## Опис

Підготувати початковий frontend flow для створення та редагування `Project`.

Оскільки логіка створення project поки проста, окрему сторінку створення не реалізовувати. Замість цього потрібно використати `PrimeVue Dialog` і винести upsert form у окремий widget component.

## Контекст

- API та типи для API вже визначені.
- Дизайн сторінки створення projects можна використовувати як орієнтир:
  `.project_office/2_design/concept/create_project_page`
- Upsert dialog має стати базовим прикладом для наступних однотипних create/update dialogs у milestone.
- Після успішного створення або редагування project потрібно оновити табличне представлення projects.

## Вимоги

- Реалізувати upsert dialog для `Project` через `PrimeVue Dialog`.
- Винести dialog у widget:
  `resources/js/widgets/projects/upsert-dialog`
- Для dialog створити composable, який врапить логіку відкриття, закриття, submit, initial values та mode.
- Використати існуючий API request для створення project.
- Використати існуючий API request для оновлення project.
- Створити mutation для створення project на рівні:
  `resources/js/entities/project/mutations`
- Створити mutation для оновлення project на рівні:
  `resources/js/entities/project/mutations`
- Зберігати mutation/query invalidation keys через існуючу project config структуру:
  `resources/js/entities/project/config`
- Після успішного створення або оновлення project оновити або інвалідовувати список projects.
- Відображати loading state під час submit.
- Відображати validation errors, які повертає API.
- Не реалізовувати delete flow у межах цієї задачі.

## Приклад Mutation

```ts
import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { createProject } from '../api/project.api'
import { ProjectQueryKey } from '../config'

export function useCreateProjectMutation() {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: createProject,
        onSuccess: async () => {
            await queryClient.invalidateQueries({
                queryKey: ProjectQueryKey.all,
            })
        },
    })
}
```

## Приклад Update Mutation

```ts
import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { updateProject } from '../api/project.api'
import { ProjectQueryKey } from '../config'

export function useUpdateProjectMutation() {
    const queryClient = useQueryClient()

    return useMutation({
        mutationFn: updateProject,
        onSuccess: async () => {
            await queryClient.invalidateQueries({
                queryKey: ProjectQueryKey.all,
            })
        },
    })
}
```

## Acceptance Criteria

- Користувач може відкрити upsert dialog для `Project`.
- Dialog реалізований через `PrimeVue Dialog`.
- Dialog винесений у `resources/js/widgets/projects/upsert-dialog`.
- Для dialog створений composable з логікою відкриття, закриття, submit, initial values та mode.
- Користувач може заповнити форму створення project.
- Користувач може заповнити форму редагування project.
- Submit форми в create mode викликає create project API request.
- Submit форми в update mode викликає update project API request.
- Create/update flows використовують `@tanstack/vue-query` mutations.
- Mutation для створення project розміщена в `resources/js/entities/project/mutations`.
- Mutation для оновлення project розміщена в `resources/js/entities/project/mutations`.
- Після успішного створення або оновлення список projects оновлюється або інвалідовується.
- Loading state відображається під час submit.
- Validation errors з API відображаються у формі.
- Delete flow не реалізований в межах задачі.

## Questions

- Які поля project мають бути у першій версії форми?
- Чи потрібна cancel action у dialog footer?
- Чи потрібно блокувати повторний submit під час pending state?

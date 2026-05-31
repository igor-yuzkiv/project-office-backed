---
type: task
status: todo
milestone_number: 3
milestone_name: Frontend CRUD Initial
milestone_path: .project_office/1_milestones/003 - CRUD
task_number: 002
task_name: Project Create View
task_path: .project_office/1_milestones/003 - CRUD/tasks/002 - Project Create View.md
created_at: 2026-05-31
updated_at: 2026-05-31
---

# 002 - Project Create View

## Milestone

- Number: 3
- Name: Frontend CRUD Initial
- Path: `.project_office/1_milestones/003 - CRUD`
- Plan: `.project_office/1_milestones/003 - CRUD/plan.md`

## Task

- Number: 002
- Name: Project Create View
- Status: todo
- Path: `.project_office/1_milestones/003 - CRUD/tasks/002 - Project Create View.md`

## Опис

Підготувати початковий frontend flow для створення `Project`.

У межах цієї задачі потрібно реалізувати create view/form для project з використанням існуючого API contract та frontend інфраструктури.

## Контекст

- API та типи для API вже визначені.
- Дизайн сторінки створення projects знаходиться тут:
  `.project_office/2_design/concept/create_project_page`
- Форма створення project має стати базовим прикладом для наступних однотипних create/update forms у milestone.
- Після успішного створення project потрібно повернути користувача до табличного представлення або іншого узгодженого екрану.

## Вимоги

- Реалізувати сторінку або view для створення `Project`.
- Використати існуючий API request для створення project.
- Створити mutation для створення project на рівні:
  `resources/js/entities/project/mutations`
- Зберігати mutation/query invalidation keys через існуючу project config структуру:
  `resources/js/entities/project/config`
- Після успішного створення project оновити або інвалідовувати список projects.
- Відображати loading state під час submit.
- Відображати validation errors, які повертає API.
- Не реалізовувати edit flow у межах цієї задачі.
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

## Acceptance Criteria

- Користувач може відкрити create view для `Project`.
- Користувач може заповнити форму створення project.
- Submit форми викликає create project API request.
- Create flow використовує `@tanstack/vue-query` mutation.
- Mutation для створення project розміщена в `resources/js/entities/project/mutations`.
- Після успішного створення список projects оновлюється або інвалідовується.
- Loading state відображається під час submit.
- Validation errors з API відображаються у формі.
- Edit і delete flows не реалізовані в межах задачі.

## Questions

- Куди саме редіректити користувача після успішного створення project?
- Які поля project мають бути у першій версії форми?
- Чи потрібна cancel action у create view?
- Чи потрібно блокувати повторний submit під час pending state?

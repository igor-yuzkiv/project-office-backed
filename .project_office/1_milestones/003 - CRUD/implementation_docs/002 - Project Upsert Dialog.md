# 002 - Project Upsert Dialog

## Що реалізовано

Upsert dialog для створення та редагування Project через PrimeVue Dialog. Composable керує стейтом на рівні сторінки, dialog — презентаційний компонент.

## Змінені файли

| Дія | Файл |
|---|---|
| New | `resources/js/entities/project/mutations/use.create-project.mutation.ts` |
| New | `resources/js/entities/project/mutations/use.update-project.mutation.ts` |
| Modified | `resources/js/entities/project/mutations/index.ts` |
| New | `resources/js/widgets/projects/upsert-dialog/composables/use.project-upsert-dialog.ts` |
| New | `resources/js/widgets/projects/upsert-dialog/ui/ProjectUpsertDialog.vue` |
| New | `resources/js/widgets/projects/upsert-dialog/index.ts` |
| New | `resources/js/shared/types/api.types.ts` |
| Modified | `resources/js/shared/types/index.ts` |
| Modified | `resources/js/shared/api/api.error.ts` |
| Modified | `resources/js/pages/projects/ProjectsPage.vue` |

## Ключові рішення

### Презентаційний dialog

`ProjectUpsertDialog.vue` не підключає жодного composable. Приймає props (`visible`, `mode`, `name`, `validationErrors`, `isPending`), емітить `update:visible`, `update:name`, `submit`. Вся логіка — у composable на рівні сторінки.

### useProjectUpsertDialog

Composable керує: `visible`, `mode` (computed з `editingProject`), `name`, `validationErrors`, `isPending`. Методи: `open(project?)`, `close()`, `submit()`. `open` без аргументу → create mode; з `IProject` → update mode з заповненими полями.

### Update mutation

`mutationFn` приймає `{ id: string; data: IUpdateProjectInput }` — обгортка над `updateProjectRequest(id, data)`, щоб відповідати сигнатурі `useMutation`.

### Validation errors

Новий тип `LaravelValidationErrors = Record<string, string[]>` у `shared/types/api.types.ts`. Використовується в `ApiError.validationErrors` та composable. `handleError` в composable перевіряє `error instanceof ApiError && error.isValidationError`.

### Підключення на сторінці

```vue
<ProjectUpsertDialog
    v-model:visible="upsertDialog.visible.value"
    v-model:name="upsertDialog.name.value"
    :mode="upsertDialog.mode.value"
    :validation-errors="upsertDialog.validationErrors.value"
    :is-pending="upsertDialog.isPending.value"
    @submit="upsertDialog.submit"
/>
```

New Project (header action) → `upsertDialog.open()`. Edit (row menu) → `upsertDialog.open(selectedProject)`.

## Перевірки

- `npm run types:check` — без помилок

## Commit message

```
feat(projects): add project upsert dialog with create and update mutations
```

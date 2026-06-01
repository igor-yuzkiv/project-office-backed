# 001 - Prepare Layout System

## Що реалізовано

Система layouts на основі `route.meta.layout`. App.vue динамічно підбирає layout-компонент за назвою з маршруту. Також підключено PrimeVue Toast і ConfirmDialog на рівні App.vue.

## Змінені файли

| Файл | Що зроблено |
|---|---|
| `resources/js/app/layouts/DefaultLayout.vue` | Новий. Wrapper `flex h-screen w-full overflow-hidden` зі `<slot />` |
| `resources/js/app/layouts/AuthLayout.vue` | Новий. Wrapper `flex h-screen w-full items-center justify-center` зі `<slot />` |
| `resources/js/app/layouts/index.ts` | Новий. `AppLayoutName` union type + `AppLayoutComponentMap` |
| `resources/js/app/App.vue` | Замінено `<router-view />` на динамічний layout з `<router-view />` всередині. Додано `<Toast />` та `<ConfirmDialog />` |
| `resources/js/app/router/index.ts` | Додано `meta.layout: 'auth'` та `meta.layout: 'default'` до маршрутів |
| `resources/js/router.d.ts` | Додано `layout?: AppLayoutName` до `RouteMeta` |

## Важливі рішення

- Layout визначається через `route.meta.layout`; fallback — `AppLayoutComponentMap.default`
- `AppLayoutName` — строгий union type `'default' | 'auth'`, підтягується в `RouteMeta` через `router.d.ts`
- Стилі через Tailwind utility classes, без scoped CSS
- `ToastService` та `ConfirmationService` вже зареєстровані в `prime-vue.plugin.ts` — в App.vue додано лише компоненти `<Toast />` і `<ConfirmDialog />`

## Перевірки

- `npx vue-tsc --noEmit` — без помилок

## Для наступного агента

- Layouts поки порожні контейнери. Sidebar, header та інший вміст DefaultLayout додаватимуться в наступних tasks.
- `AppLayoutComponentMap` розширюється простим додаванням нового ключа в `index.ts` і компонента поруч.

## Commit message

```
feat(frontend): add layout system with default and auth layouts
```

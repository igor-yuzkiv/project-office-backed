
## Goals

- Підготувати базу фронтенду для подальшої розробки.
- Реалізувати систему Layouts.
- Реалізувати базові екрани для CRUD-операцій, які вже існують на бекенді.
- Підготувати початковий дизайн системи.

Концепт дизайну та бажаний вигляд компонентів знаходиться тут:

`/var/www/task-manager/mvp-task-manager/.project_office/2_design/concept`

---

## Layout System

- Auth Layout
- Default Layout

Підготувати базову інфраструктуру для роботи з layout-ами.
---

## Screens

Поки що потрібно лише створити сторінки та налаштувати маршрути.

### Home

### Projects

### Tasks

### Documents

---

## App Header

Мене влаштовує загальний зовнішній вигляд із концепту, але потрібна спрощена версія.

Header складається з двох основних секцій.

### Left Section
- Page title
- Title передається через props

### Right Section

Пошук поки не реалізовувати.

Елементи:

- User avatar
- Notifications icon (placeholder)
- Settings icon
- Split Button для дій

### Split Button

- Список actions передається через props.
- Якщо actions не передано — кнопка не відображається.

### Icon Button

Для notification та settings icon створити:

`shared/components/button/IconButton.vue`

Додаю приклад реалізації.

Налаштування та утиліти для розмірів і типів винести за межі компонента для перевикористання:

```text
shared/components/button/
├── IconButton.vue
├── config/
└── types/
```

Example:

```vue
<script setup lang="ts">  
import Button from 'primevue/button'  
import type { ButtonProps } from 'primevue/button'  
import { Icon } from '@iconify/vue'  
import { computed } from 'vue'  
import type { ButtonSize } from '../../types'  
  
const ICON_SIZE_MAP = {  
    xsmall: 'h-3! w-3',  
    small: 'h-4 w-4',  
    medium: 'h-5 w-5',  
    large: 'h-6 w-6',  
} as const  
  
const BUTTON_SIZE_MAP = {  
    xsmall: 'h-4 w-4',  
    small: 'h-6 w-6',  
    medium: 'h-8 w-8',  
    large: 'h-10 w-10',  
} as const  
  
interface IconButtonProps extends /* @vue-ignore */ Omit<ButtonProps, 'size'> {  
    icon: string  
    loading?: boolean  
    loadingIcon?: string  
    size?: ButtonSize  
}  
  
const props = withDefaults(defineProps<IconButtonProps>(), {  
    severity: 'info',  
    loading: false,  
    loadingIcon: 'line-md:loading-loop',  
    size: 'small',  
    tooltip: () => ({}),  
})  
  
function resolveFromMap(size: ButtonSize | undefined, map: Record<ButtonSize, string>): string {  
    if (!size) {  
        return ''  
    }  
  
    return map[size] ? map[size] : ''  
}  
  
const iconSize = computed<string>(() => resolveFromMap(props.size, ICON_SIZE_MAP))  
const buttonSize = computed<string>(() => resolveFromMap(props.size, BUTTON_SIZE_MAP))  
</script>  
  
<template>  
    <!-- @vue-ignore: We need to bind the size prop to the Button component, but we don't want it to affect our computed buttonSize class -->  
    <Button :class="['flex shrink-0 grow-0 items-center justify-center', buttonSize]" v-bind="{ ...$attrs, size: '' }">  
        <template #icon>  
            <Icon v-if="loading" :icon="loadingIcon" :class="iconSize" />  
            <Icon v-else :icon="icon" :class="iconSize" />  
        </template>  
    </Button>  
</template>  
  
<style scoped></style>
```

---

## App Left Navigation Sidebar

Основна навігація по системі.
Можна орієнтуватися на концепт: `tasks_kanban_page.png`

### Navigation Items

Поки всі пункти є заглушками:

- Home
- Projects
- Tasks
- Documents
    

### Recent Projects

Окрема секція в сайдбарі. Поки реалізувати як placeholder.

Поточний дизайн із концепту мене влаштовує:
- colored circle  
- project name
    

В майбутньому планую додати до проєкту власний колір з автоматичною генерацією або використовувати колір статусу.

### Bottom Section

В нижній частині сайдбара зарезервувати місце для майбутніх елементів:
- Settings
- User Profile

Поки без реалізації функціоналу.

---

# Layout Infrastructure

## use.app-layout.store.ts

Створити стор, який відповідатиме за взаємодію між сторінками та layout-компонентами.

### Structure

- Винести стор на рівень `app/stores/*`
    
- Поточний `auth.store` також перенести в цю папку
    
- Перейменувати згідно конвенції:
    

```text
use.auth.store.ts
use.app-layout.store.ts
```

### Responsibilities

- Зберігання стану layout-компонентів
    
- Взаємодія сторінок із layout-компонентами
    
- Керування App Header
    
- Керування Sidebar (у майбутньому)
    

---

## App Title

Стан заголовка сторінки, який буде відображатися в App Header.

Вимоги:

- title зберігається в store
    
- title відображається в App Header
    
- title автоматично додається до browser page title
    
- формат page title:
    

```text
<Page Title> | <VITE_APP_NAME>
```

---

## Header Actions

Стан, який відповідає за Split Button в App Header.

Структура:

```ts
{
    key: string
    title: string
    action: () => void
    is_primary?: boolean
}
```

Store зберігає масив actions.

Цей масив передається в App Header для рендерингу Split Button.

Приклад:

```ts
[
    {
        key: 'create-project',
        title: 'Create Project',
        action: () => {},
        is_primary: true,
    },
]
```

---

## Confirmed Clarifications

- `AppHeader` не читає layout store напряму. Компонент отримує `title` та `actions` через props.
- `DefaultLayout` підключається до `use.app-layout.store.ts` і передає дані в `AppHeader`.
- `Settings` у header поки є кнопкою без логіки переходу.
- Sidebar navigation може містити route links для сторінок, які вже створені в межах milestone.
- Застарілу task 002 видалено і не використовувати як актуальний scope.
- Перенесення `auth.store` в `app/stores` винесено в окрему task.

---

## Task Split

### X - 001 - Prepare Layout System

Статус: completed.

Scope:
- layout selection через `route.meta.layout`;
- `DefaultLayout`;
- `AuthLayout`;
- layout typing для router meta.

### 002 - App Shell Components

Scope:
- `AppHeader` як presentation component;
- `AppLeftNavigationSidebar`;
- `IconButton` у `shared/components/button/`;
- placeholder user avatar, notifications button, settings button;
- Split Button для header actions через props;
- sidebar placeholders для Recent Projects і bottom section.

Out of scope:
- app layout store;
- auth store move;
- CRUD screens;
- settings route або settings logic.

### 003 - App Layout Store

Scope:
- створити `resources/js/app/stores/use.app-layout.store.ts`;
- зберігати page title;
- зберігати header actions;
- синхронізувати browser page title у форматі `<Page Title> | <VITE_APP_NAME>`;
- підключити store у `DefaultLayout` і передати дані в `AppHeader`.

### 004 - Move Auth Store To App Stores

Scope:
- перенести `resources/js/stores/auth.store.ts` у `resources/js/app/stores/use.auth.store.ts`;
- оновити imports у router, pages та інших місцях використання;
- не змінювати auth behavior.

### 005 - Create Base Pages And Routes

Scope:
- створити базові сторінки `Projects`, `Tasks`, `Documents`;
- додати routes для створених сторінок;
- підключити `meta.layout: 'default'`;
- залишити сторінки як мінімальні placeholders.

### 006 - Wire Sidebar Navigation

Scope:
- зробити sidebar navigation route-aware;
- додати links для `Home`, `Projects`, `Tasks`, `Documents`;
- додати active state для поточного route;
- не додавати settings route без окремого погодження.

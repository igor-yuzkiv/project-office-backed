
# Core Features and Entities

- Project
- Task List
- Task
- Issue/Bug
- Sub Tasks
- comments
- tags
- time logs
- система кастомних полів та шаблоноів для задача чи проектів
	- напевно таблиця в якій будуть поля для усіх сутностей
- система кастомізації метадани: кастомні статуси задач тощо
- documentation hub: ідея щоб проект окрім керування задачам і проектами давав ще функціонал для вердення та збереження документацій по проектах з можливість привязувати документи до тасок та проктів, з окреми інтерфейсом для читання та формування документації так із інтегрованим інтерфейсом в задачу чи в проект

# User

- auth: regular logn, sso, zoho auth
- push notifications
- roles and permissions: entity based, ex Projects: Create, Update, Delete, Read
- Mention user in comment, task/project description


# ?? Tasks Strategy

- таска має мати можливість розідлятись на саб таски але не зовсім
- корче має бути можливсть розідляти роботу по ролях PM, QA, DEV але це буде все ще одна таска з спільним time log


# AI Asistam or AI Features


# Integrations

- two way sync with zoho projects


## Tags

- Create Tags entity.
- Implement Tags CRUD API.
- Add polymorphic relations for attaching tags to supported entities.
- Tags мають підтримувати entity-level прив'язку через polymorphic relations.
- Exact API contracts, validation rules і task decomposition потрібно уточнити перед реалізацією.

# Recent Projects

- додати hadnler/query GetProjectHandler який відповідає за відображення деталей по проекту, поки вератиме тільки модель але в персепективі можна DTO якщо буде якась специфічна вибірка з релейшенами чи агрегацією
- і додати сервіс типу TackRecentProjectsService чи RecentProjectService який буде збергати в бд запис про відвідування прокту юзером
- він має зберігати історію усіх відвідувань
- але треба якось подумати за ротацю даних щоб вони не накопичувались занадто багато
- і на фронті показувати в меню недавно відвідані проекти
- думаю це можна зробити через окрмий vue-query і інвалідувати ключи коли користувач заходить в проект що автоматично тригрне рефеч і оновить список
- 
# URL State

- Реалізувати URL-based filters and sort.


# Custom View
- типу як в срм щоб користувачі створвали відфільтровані вю 

# інтегарція з аі для керування
типу як моє воркфлоу з планування робивання на задчі і implementation doc
але через mvp та фіксуванням результатів там

# User Expiriance
- save filters and sorting
- views


# Comments
- search
- replay
- threds

# Integrations
- cliq for notifications or summary
- sentry for issue creation


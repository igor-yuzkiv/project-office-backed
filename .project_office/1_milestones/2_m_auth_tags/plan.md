# Milestone 2: Auth And Tags

## Scope

- Implement Sanctum SPA Authentication.
- Add current user API through `GET /me`.
- Add login API through `POST /login`.
- Add logout API through `POST /logout`.
- Create Tags entity.
- Implement Tags CRUD API.
- Add polymorphic relations for attaching tags to supported entities.

## Notes

- Auth має працювати через Laravel Sanctum SPA Authentication.
- `GET /me` має повертати current user через User JSON resource.
- Tags мають підтримувати entity-level прив'язку через polymorphic relations.
- Exact API contracts, validation rules і task decomposition потрібно уточнити перед реалізацією.

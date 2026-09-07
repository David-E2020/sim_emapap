# Frontend Conventions (Vue / Laravue)

## Component Structure & UI Framework
- Project uses Vue 2 with Laravue layout and Element UI (`el-*` components) / BootstrapVue.
- Views reside in `resources/js/src/views/{modulo}/{Componente}.vue`.
- Follow the consistent data-table layout:
  - Filter and action bar at the top (`el-card`, `el-input`, `el-button`).
  - `el-table` with pagination (`el-pagination`) synced with backend query parameters (`per_page`, `page`, `search`).
  - Dialog modals (`el-dialog`) for CRUD forms with validation (`el-form` + `el-form-item` with `rules`).

## API Communication
- Axios requests to backend routes prefixed with `/api/...` sending JWT Bearer authorization headers.
- Handle responses matching `{ success: true, data: [...] }`.
- Toast notifications using Element UI `this.$message.success(...)` and `this.$message.error(...)`.

# Auto actions (Autos/Show): how to add a new button/action

This project renders the **Actions** on `resources/js/Pages/Autos/Show.vue` from the backend.
The frontend does **not** decide permissions by itself.

The flow is:

1. **Backend** computes allowed actions for a given auto + user:
   - `App\Services\Autos\AutoActionsResolver`
2. Backend passes them to the page:
   - `App\Http\Controllers\Client\AutoController@show` → Inertia prop `actions`
3. **Frontend** renders actions:
   - `resources/js/Components/Autos/AutoActionsPanel.vue`
4. Click opens either:
   - transition modal (`AutoTransitionsModal`) and submits transition, or
   - another UI modal (example: storage cost)
5. Transition submit goes to:
   - `POST /autos/{auto}/transitions` → `Client\AutoTransitionController@store`
6. Store is protected by:
   - `TransitionRequest::authorize()` (policy)
   - `AutoActionsResolver::canPerformTransition()` (status + granular permission)

---

## Two types of actions

### A) Transition action (server state change)
Examples: `move_to_customs`, `sell`, `save_files`.

Characteristics:

- Has `action` string in request.
- Is validated in `TransitionRequest`.
- Is executed by a transition service implementing `App\Services\Autos\Transitions\AutoTransition`.

### B) UI-only action (no transition endpoint)
Example: `storage_cost`.

Characteristics:

- Does **not** go through `AutoTransitionController`.
- Is handled in `Show.vue` (`handleAction`) and can load data via `fetch()`.

---

## Adding a NEW TRANSITION action (end-to-end checklist)

Below is the recommended order.

### 1) Pick an action key

- Use snake_case.
- Example: `mark_as_ready`.

This key must be consistent in **all** places.

### 2) Backend: add action + permission constants

File: `app/Services/Autos/AutoActionsResolver.php`

- Add:
  - `public const ACTION_MARK_AS_READY = 'mark_as_ready';`
  - `public const PERMISSION_TRANSITION_MARK_AS_READY = 'transition_mark_as_ready';`

### 3) Backend: add permission/status logic

File: `app/Services/Autos/AutoActionsResolver.php`

- In `canPerformTransition()` add a new `match` branch:
  - Check the auto status(es) where it is allowed.
  - Check granular permission: `$user->can(self::PERMISSION_TRANSITION_MARK_AS_READY)`.

- In `resolve()` add the action into `$actions[]` if allowed:
  - `['key' => self::ACTION_MARK_AS_READY, 'label' => '...', 'variant' => '...']`

Notes:

- The resolver already enforces **policy `update`** first.
- Only put logic here that affects what button appears + whether transition is allowed.

### 4) Backend: create permission and assign to roles

File: `database/seeders/RoleAndPermissionSeeder.php`

- Add permission constant to `$transitionPermissions` list.
- Decide which roles should have it.

Important:

- The seeder class is **`RoleAndPermissionSeeder`**.
- Run it like:

```bash
php artisan db:seed --class=RoleAndPermissionSeeder
```

### 5) Backend: request validation

File: `app/Http/Requests/Autos/TransitionRequest.php`

- Add the new action to the `in:` rule for `action`.
- Add per-action rules into the `switch ($action)` if needed.

### 6) Backend: transition execution service

Folder: `app/Services/Autos/Transitions/`

- Create a new service class implementing `AutoTransition`.
- Implement `handle(Auto $auto, array $data, User $actor): void`.

Keep it SOLID/DRY:

- Only implement logic for this transition.
- Use DB transactions when multiple writes are involved.

### 7) Backend: controller mapping

File: `app/Http/Controllers/Client/AutoTransitionController.php`

- Add a new entry into `$map`:
  - `'mark_as_ready' => MarkAsReadyTransition::class,`

This controller already:

- returns 422 if unknown action
- returns 403 if resolver says the action is not allowed

### 8) Frontend: button click handler

File: `resources/js/Pages/Autos/Show.vue`

- Add new `case` to `handleAction(key)`:
  - call a new opener function (`openMarkAsReady()`)

- Add opener function:
  - set `form.action = 'mark_as_ready'`
  - set `transition.action = 'mark_as_ready'`
  - open modal: `transition.open = true`

### 9) Frontend: modal UI for the new action

File: `resources/js/Components/Autos/AutoTransitionsModal.vue`

- Add a new `template v-else-if="action === 'mark_as_ready'"`.
- Add required inputs bound to `form.*`.
- Add title mapping in the `title` computed.
- If the action needs required fields, update `submitDisabled` accordingly.

### 10) Frontend: form payload

File: `resources/js/Pages/Autos/Show.vue`

- Ensure `useForm({ ... })` contains fields required by the new action.
- If you need file uploads, reuse existing `upload` and `Uploads` component.

### 11) Manual test checklist

- Confirm the action appears only when:
  - auto is in correct status
  - user has the granular permission
  - user passes `AutoPolicy@update`

- Try calling transition endpoint without permission (should be **403**).
- Try sending unknown action (should be **422**).

---

## Adding a NEW UI-only action (example: storage cost)

1. Backend: add an entry in `AutoActionsResolver::resolve()`:
   - key example: `storage_cost` (UI key)
   - permission example: `view_storage_cost`

2. Frontend: in `Show.vue` handle it in the `if (key === 'storage_cost')` block.

3. No `TransitionRequest` / `AutoTransitionController` changes.

---

## Troubleshooting

### The seed command fails: “Target class ... does not exist”

Make sure you run the correct seeder class name:

```bash
php artisan db:seed --class=RoleAndPermissionSeeder
```

(not `RolesAndPermissionsSeeder`).

# Client Blade Migration — Documentation

## Overview

This document describes the completed migration of the client-facing pages from Vue/Inertia to Blade + Alpine, the reusable upload refactor, and related fixes.

## Goals

- Migrate client UI to Blade templates.
- Keep Vue/Inertia fallback for safe rollout.
- Preserve existing behavior and permissions.
- Improve file upload UX (single dropzone, previews, deletion, progress).
- Keep regression risk low with feature tests.

## Feature Flag Rollout

A feature flag controls Blade rendering:

- `features.client_blade_enabled`
- File: `config/features.php`

When enabled, client pages render Blade views. When disabled, existing Inertia pages continue to work.

## Backend Rendering Changes

### Home
- File: `app/Services/Client/HomeService.php`
- `index()` now returns Blade view in flag-enabled mode, Inertia otherwise.

### Autos
- File: `app/Http/Controllers/Client/AutoController.php`
- `index/create/show` use Blade when flag is on.
- `store` flow preserved; validation and redirects unchanged.

### Profile
- File: `app/Http/Controllers/Client/ProfileController.php`
- Blade rendering added under feature flag.

### Routes
- File: `routes/user.php`
- Client routes kept intact; instructions/profile/autos operate in Blade mode when enabled.

## Blade Pages Added/Updated

- `resources/views/client/layouts/app.blade.php`
  - Client layout, menu interactions, VIN search debounce with Alpine.
- `resources/views/client/home.blade.php`
- `resources/views/client/autos/index.blade.php`
- `resources/views/client/autos/create.blade.php`
- `resources/views/client/autos/show.blade.php`
- `resources/views/client/instructions.blade.php`
- `resources/views/client/profile.blade.php`

## Reusable Upload Component

### Component
- File: `resources/views/client/components/file-upload-box.blade.php`

### Capabilities
- Unified drag-and-drop + button picker.
- Supports photos/videos/documents in one queue.
- Preview cards for photos/videos, filename tile for documents.
- Per-item deletion.
- Upload progress bar.
- Hidden synchronized inputs:
  - `photos[]`
  - `videos[]`
  - `documents[]`

### Integration Points
- Create page: `resources/views/client/autos/create.blade.php`
- Show transitions modal: `resources/views/client/autos/show.blade.php`

## Shared Alpine Upload Logic

- File: `resources/js/client-blade.js`
- Global factory: `window.createUnifiedUploadState()`

### State & Methods
- State:
  - `fileQueue`
  - `uploading`
  - `uploadProgress`
- Methods:
  - `openPicker()`
  - `onPick(event)`
  - `onDrop(event)`
  - `addFiles(fileList)`
  - `kindFor(file)`
  - `removeFile(id)`
  - `countByKind(kind)`
  - `syncInputs()`
  - `clearUploadState()`
  - `submitWithProgress(event)`

## Critical Bug Fix: 405 on Upload

### Symptom
On transitions upload, request URL became:

`/autos/[object HTMLInputElement]`

Result: `405 Method Not Allowed`.

### Root Cause
In `submitWithProgress`, code used `form.action` while form also had an input named `action`. DOM property collision returned the input element instead of the form action URL.

### Fix
In `resources/js/client-blade.js`:

- Use `event.currentTarget`.
- Guard with `instanceof HTMLFormElement`.
- Read form attributes via:
  - `form.getAttribute('action')`
  - `form.getAttribute('method')`

This removes collisions with form controls named `action`/`method`.

## Media Optimization Queue Discussion

Current media conversion behavior was intentionally left unchanged.

- File: `app/Support/MediaLibrary/AutoMediaConversions.php`
- `thumb` and `preview` remain configured as currently implemented.

Decision: do not change queue behavior in this iteration.

## Build / Runtime Notes

- Alpine client entry is bundled through Vite.
- File: `resources/js/client-blade.js`
- Ensure `npm run dev` / `npm run build` includes this entry (already configured in project).

## Test Coverage

Updated/added feature tests:

- `tests/Feature/ClientBladePagesTest.php`
- `tests/Feature/InstructionsPageTest.php`
- `tests/Feature/ProfilePageTest.php`

Executed checks during implementation:

- `php artisan test --filter=ClientBladePagesTest`
- `php artisan test --filter="ClientBladePagesTest|ProfilePageTest|InstructionsPageTest"`

All passed in the implementation session.

## Troubleshooting

### Upload button does nothing / wrong URL
- Verify latest `resources/js/client-blade.js` is built.
- Rebuild assets and hard refresh browser.

### Blade component not found
- Ensure file exists:
  - `resources/views/client/components/file-upload-box.blade.php`
- Include syntax:
  - `@include('client.components.file-upload-box')`

### Conversions not appearing immediately
- Expected behavior if conversions are queued in environment.
- Start queue worker when needed:
  - `php artisan queue:work`

## Summary

The client area now supports a Blade + Alpine path with feature-flag rollout, reusable upload UX, shared upload state, and fixed transitions upload submission reliability, while preserving fallback compatibility and test coverage.

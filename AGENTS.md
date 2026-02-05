# AGENTS.md

This file provides guidance to coding agents when working with code in this repository.

## Project Overview

**Statamic Admin Bar** - A frontend admin bar for managing Statamic content directly from the site.

This is the **addon source code** for `el-schneider/statamic-admin-bar`.

## Folder Structure

The addon is developed alongside sibling sandbox projects:

```
../statamic-admin-bar-test/         # Statamic v5 sandbox
../statamic-admin-bar-test-v6/      # Statamic v6 sandbox
```

## Tech Stack

- **Backend:** PHP, Laravel, Statamic CMS (v5 + v6)
- **Frontend:** Vue 3, TypeScript, Tailwind CSS 3, Radix Vue
- **Build:** Vite with laravel-vite-plugin
- **Entry points:** `resources/js/admin-bar.ts`, `resources/css/admin-bar.css`
- **Output:** `resources/dist/`

## Development Commands

### Code Quality

```bash
npm run check          # Run all checks (format, eslint, pint)
npm run fix            # Run all fixes (format, eslint, pint)
npm run format:check   # prettier --check .
npm run format:fix     # prettier --write .
npm run eslint:check   # eslint .
npm run eslint:fix     # eslint . --fix
npm run pint:check     # ./vendor/bin/pint --test
npm run pint:fix       # ./vendor/bin/pint
```

### Building

```bash
npm run build   # Production build
npm run dev     # Dev server with HMR
```

### Testing

```bash
./vendor/bin/pest
./vendor/bin/pest --filter=SomeTest
```

### Pre-commit Hook

Husky runs checks on commit. Do not bypass it.

## Integration Testing with Sandboxes

Both sandboxes symlink this addon. Changes to addon files are immediately reflected.

| Sandbox | URL | Statamic Version |
|---------|-----|------------------|
| v5 | `http://statamic-admin-bar-test.test` | v5 |
| v6 | `http://statamic-admin-bar-test-v6.test` | v6 |

**Credentials:**

- Email: `agent@agent.md`
- Password: `agent`
- Login URL: `http://statamic-admin-bar-test.test/cp` (or `-v6`)

To update the addon in a sandbox after `composer.json` changes:

```bash
cd ../statamic-admin-bar-test && composer update el-schneider/statamic-admin-bar
cd ../statamic-admin-bar-test-v6 && composer update el-schneider/statamic-admin-bar
```

## Usage in Templates

Add the `admin_bar` tag after the opening `<body>` tag:

```antlers
{{ admin_bar }}
```

## Off-Limits Files

- **`resources/dist/`** - Built output. Regenerate with `npm run build`.
- **`vendor/`** - Managed by Composer.
- **`node_modules/`** - Managed by npm.

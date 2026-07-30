## Project Overview

**Statamic Admin Bar**. A frontend admin bar for managing Statamic content directly from the site.

## Tech Stack

- **Backend:** PHP, Laravel, Statamic CMS (v5 + v6)
- **Frontend:** Vue 3, TypeScript, Tailwind CSS 3, Radix Vue
- **Build:** Vite with laravel-vite-plugin
- **Entry points:** `resources/js/admin-bar.ts`, `resources/css/admin-bar.css`
- **Output:** `resources/dist/`

## Development Commands

### Code Quality

```bash
npm run check   # prettier --check, eslint, pint --test
npm run fix     # the same three, writing
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

## Integration Testing

Verifying admin bar changes in a browser needs a Statamic app (v5 and v6) with this addon installed as a path repository.

## Usage in Templates

Add the `admin_bar` tag after the opening `<body>` tag:

```antlers
{{ admin_bar }}
```

## Contributing

- Comments say why, not what changed. History belongs in the PR.
- UI changes: verify in a real browser (agent-browser, Chrome DevTools) and say what you checked. No browser automation available — ask, don't guess.
- Add nothing you can derive or reuse.
- Fix the cause, not the reported symptom.
- No abstraction with a single caller.
- Let failures surface. No try/catch for tidiness.

## Off-Limits Files

- **`resources/dist/`** — Built by CI on push to `main`. Do NOT commit build output.
- **`CHANGELOG.md`** — Updated by CI on release. Do NOT edit.

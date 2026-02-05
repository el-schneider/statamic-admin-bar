# Changelog

## v0.3.1 - 2026-02-05

### Security

- Fix 8 npm security vulnerabilities (1 critical, 2 high, 4 moderate, 1 low):
  - **Critical:** `form-data` unsafe random function for boundary generation
  - **High:** `axios` SSRF/credential leakage and DoS via missing data size check
  - **High:** `glob` CLI command injection
  - **Moderate:** `esbuild`/`vite` dev server cross-origin request vulnerability
  - **Moderate:** `js-yaml` prototype pollution in merge
  - **Moderate:** `lodash` prototype pollution in `_.unset` and `_.omit`
  - **Low:** `brace-expansion` ReDoS
  

### Dependencies

- Update all npm packages to latest versions within semver ranges
- Rebuild production assets

## v0.3.0 - 2026-02-05

### What's new

- Statamic v6 support — the addon now works with both Statamic v5 and v6

**Full Changelog**: https://github.com/el-schneider/statamic-admin-bar/compare/v0.2.1...v0.3.0

## v0.2.1 - 2025-06-05

### What's fixed

- accessing the avatar url no longer yields an error

**Full Changelog**: https://github.com/el-schneider/statamic-admin-bar/compare/v0.2.0...v0.2.1

## v0.2.0 - 2025-02-16

### What's fixed

- Already logged in user can't see admin bar

**Full Changelog**: https://github.com/el-schneider/statamic-admin-bar/compare/v0.1.9...v0.2.0

## v0.1.9 - 2025-02-16

### What's fixed

- an errounous `ray()` call
- formatting

**Full Changelog**: https://github.com/el-schneider/statamic-admin-bar/compare/v0.1.8...v0.1.9

## v0.1.8 - 2025-02-16

### What's new

- User metadata in user menu
- Refined cache menu styling

### What's fixed

- Taxonomy term routes are now handled properly by the site switcher
- Site switcher now only shows items
  - for which user has authorization
  - which are enabled in the respective taxonomy/collection
  

**Full Changelog**: https://github.com/el-schneider/statamic-admin-bar/compare/v0.1.7...v0.1.8

## v0.1.7 - 2025-02-15

### What's new

- Admin Bar is now displayed on mobile
- Space-aware hiding/showing of elements
- Refined site switcher
- Added a new preference option to choose whether the locale or site name is used in labels

**Full Changelog**: https://github.com/el-schneider/statamic-admin-bar/compare/v0.1.6...v0.1.7

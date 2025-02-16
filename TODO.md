# TODO

- [x] Implement permissions
- [x] Add admin-bar preferences (by user)
    - [x] Dark mode
- [x] Add multi-site switcher
- [x] Intelligently "unhide" admin bar for previous users, based on localStorage
- [x] Admin bar to respect users language preference

## Fixes

- [x] Fix missing translations
- [x] 'All entries' should probably link to the "filtered by site" version
- [x] Fully test "entries" features with terms
- [x] Fully test and implement "scheduled" and "expired" entries

## Features

- [x] Add a menu for clearing caches
- [x] Add mobile menu
- [ ] Add more preferences
    - [ ] Add "color scheme"
    - [ ] Maybe add option to hide Site Switcher (maybe there are multisites, that never have an "origin" relation)
- [ ] Add button to hide admin bar
- [ ] Search
- [ ] Add dialog for choosing an origin when creating a new entry

## Optimizations

- [ ] DRY up shadcn components, remove unused variants
    - [ ] Use button component for menu items
- [x] Exclude admin-bar from SSG setups
- [x] Use site name, instead of the locale, as label for the entry switcher button
- [ ] Add tooltips and/or aria-labels
- [ ] Translate preferences instructions
- [ ] Lazy-load admin bar after checking permissions

## Investigate

- [ ] stand-alone npm package for decoupled setup
- [ ] ways to share auth between (sub)domains

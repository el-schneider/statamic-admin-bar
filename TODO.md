# TODO

- [x] Implement permissions
- [x] Add admin-bar preferences (by user)
    - [x] Dark mode
- [x] Add multi-site switcher
- [x] Intelligently "unhide" admin bar for previous users, based on localStorage
- [x] Admin bar to respect users language preference

## Fixes

- [x] Fix missing translations
- [ ] All entries should probably link to the "filtered by site" version

## Features

- [ ] Add a menu for clearing caches
- [ ] Add mobile menu
- [ ] Add more preferences
    - [ ] Add "color scheme"
    - [ ] Maybe add option to hide Site Switcher (maybe there are multisites, that never have an "origin" relation)
- [ ] Add button to hide admin bar
- [ ] Search
- [ ] Add dialog for choosing an origin when creating a new entry

## Optimizations

- [ ] DRY up shadcn components, remove unused variants
    - [ ] Use button component for menu items
- [ ] Exclude admin-bar from SSG setups

## Investigate

- [ ] stand-alone npm package for decoupled setup
- [ ] ways to share auth between (sub)domains

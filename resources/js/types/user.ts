import type { ActionItem } from './item'

interface User extends ActionItem {
    initials: string
    preferred_locale: string
    title: string
    email: string
    avatar: string | null
    roles: string[]
    groups: string[]
    roles_label: string
    groups_label: string
    is_super: boolean
    items: ActionItem[]
    preferences: {
        dark_mode: boolean
    }
}

export type { User }
